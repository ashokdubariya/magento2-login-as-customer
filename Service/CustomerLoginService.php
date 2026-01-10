<?php
/**
 * Customer Login Service
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerLoginService
 *
 * Handles programmatic customer login without password verification
 * Used exclusively for "Login as Customer" functionality
 */
class CustomerLoginService
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Login customer by ID without password verification
     *
     * Security note: This method bypasses password authentication
     * and should only be called after proper token validation
     *
     * @param int $customerId
     * @return void
     * @throws LocalizedException
     */
    public function loginCustomerById(int $customerId): void
    {
        try {
            // Fetch customer data
            $customer = $this->customerRepository->getById($customerId);

            // Verify customer is active
            if (!$customer->getId()) {
                throw new LocalizedException(__('Customer account does not exist.'));
            }

            // Set customer as logged in
            // This bypasses password check - token validation is security gate
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();

            $this->logger->info(
                'Customer logged in via Login as Customer',
                ['customer_id' => $customerId]
            );
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Customer does not exist.'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to login customer: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to login as customer.'));
        }
    }

    /**
     * Check if customer is currently logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }
}
