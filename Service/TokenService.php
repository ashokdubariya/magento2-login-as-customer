<?php
/**
 * Token Service - Core Security Component
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Service;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Ashokkumar\LoginAsCustomer\Api\Data\AuditLogInterface;
use Ashokkumar\LoginAsCustomer\Model\AuditLog;
use Ashokkumar\LoginAsCustomer\Model\AuditLogFactory;
use Ashokkumar\LoginAsCustomer\Model\Config;
use Ashokkumar\LoginAsCustomer\Model\ResourceModel\AuditLog as AuditLogResource;
use Psr\Log\LoggerInterface;

/**
 * Class TokenService
 *
 * Handles secure token generation, validation, and audit logging
 * Uses cryptographically secure random tokens with SHA-256 hashing
 */
class TokenService
{
    /**
     * Token length in bytes (32 bytes = 64 hex characters)
     */
    private const TOKEN_LENGTH = 32;

    /**
     * @var AuditLogFactory
     */
    private $auditLogFactory;

    /**
     * @var AuditLogResource
     */
    private $auditLogResource;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AuditLogFactory $auditLogFactory
     * @param AuditLogResource $auditLogResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param AdminSession $adminSession
     * @param RemoteAddress $remoteAddress
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AuditLogFactory $auditLogFactory,
        AuditLogResource $auditLogResource,
        CustomerRepositoryInterface $customerRepository,
        AdminSession $adminSession,
        RemoteAddress $remoteAddress,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->auditLogFactory = $auditLogFactory;
        $this->auditLogResource = $auditLogResource;
        $this->customerRepository = $customerRepository;
        $this->adminSession = $adminSession;
        $this->remoteAddress = $remoteAddress;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Generate secure token for customer login
     *
     * Security measures:
     * - Uses cryptographically secure random_bytes()
     * - Stores SHA-256 hash only, never plaintext
     * - Single-use token with expiration
     * - Logs admin IP and timestamp
     *
     * @param int $customerId
     * @return array ['token' => string, 'log_id' => int]
     * @throws LocalizedException
     */
    public function generateToken(int $customerId): array
    {
        // Validate customer exists
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Customer does not exist.'));
        }

        // Get current admin user
        $admin = $this->adminSession->getUser();
        if (!$admin || !$admin->getId()) {
            throw new LocalizedException(__('Unable to identify admin user.'));
        }

        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $tokenHash = hash('sha256', $token);

        // Calculate expiry time
        $currentTime = $this->dateTime->gmtTimestamp();
        $expiryTime = $currentTime + ($this->config->getTokenLifetime() * 60);

        // Create audit log entry
        /** @var AuditLog $auditLog */
        $auditLog = $this->auditLogFactory->create();
        $auditLog->setAdminId((int)$admin->getId());
        $auditLog->setAdminUsername((string)$admin->getUsername());
        $auditLog->setCustomerId($customerId);
        $auditLog->setCustomerEmail($customer->getEmail());
        $auditLog->setTokenHash($tokenHash);
        $auditLog->setIpAddress($this->remoteAddress->getRemoteAddress());
        $auditLog->setStatus(AuditLog::STATUS_PENDING);
        $auditLog->setStoreId((int)$this->storeManager->getStore()->getId());
        $auditLog->setExpiresAt(date('Y-m-d H:i:s', $expiryTime));

        try {
            $this->auditLogResource->save($auditLog);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save audit log: ' . $e->getMessage());
            throw new LocalizedException(__('Failed to generate login token.'));
        }

        return [
            'token' => $token,
            'log_id' => $auditLog->getEntityId()
        ];
    }

    /**
     * Validate token and return audit log entry
     *
     * Validates:
     * - Token hash matches stored hash
     * - Token has not expired
     * - Token has not been used (single-use)
     *
     * @param string $token
     * @return AuditLogInterface|null
     */
    public function validateToken(string $token): ?AuditLogInterface
    {
        $tokenHash = hash('sha256', $token);

        /** @var \Ashokkumar\LoginAsCustomer\Model\ResourceModel\AuditLog\Collection $collection */
        $collection = $this->auditLogFactory->create()->getCollection();
        $collection->addFieldToFilter('token_hash', $tokenHash)
            ->addFieldToFilter('status', AuditLog::STATUS_PENDING)
            ->setPageSize(1);

        /** @var AuditLog $auditLog */
        $auditLog = $collection->getFirstItem();

        if (!$auditLog->getEntityId()) {
            return null;
        }

        // Check if token has expired
        $currentTime = $this->dateTime->gmtTimestamp();
        $expiryTime = strtotime($auditLog->getExpiresAt());

        if ($currentTime > $expiryTime) {
            // Mark as expired
            $auditLog->setStatus(AuditLog::STATUS_EXPIRED);
            try {
                $this->auditLogResource->save($auditLog);
            } catch (\Exception $e) {
                $this->logger->error('Failed to update expired token: ' . $e->getMessage());
            }
            return null;
        }

        return $auditLog;
    }

    /**
     * Mark token as used successfully
     *
     * @param AuditLogInterface $auditLog
     * @return void
     */
    public function markTokenAsUsed(AuditLogInterface $auditLog): void
    {
        $auditLog->setStatus(AuditLog::STATUS_SUCCESS);
        $auditLog->setUsedAt(date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp()));

        try {
            $this->auditLogResource->save($auditLog);
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark token as used: ' . $e->getMessage());
        }
    }

    /**
     * Mark token as failed
     *
     * @param AuditLogInterface $auditLog
     * @return void
     */
    public function markTokenAsFailed(AuditLogInterface $auditLog): void
    {
        $auditLog->setStatus(AuditLog::STATUS_FAILED);

        try {
            $this->auditLogResource->save($auditLog);
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark token as failed: ' . $e->getMessage());
        }
    }
}
