<?php
/**
 * Audit Log Model
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Model;

use Magento\Framework\Model\AbstractModel;
use Ashokkumar\LoginAsCustomer\Api\Data\AuditLogInterface;

/**
 * Class AuditLog
 *
 * Represents a single audit log entry for login as customer action
 */
class AuditLog extends AbstractModel implements AuditLogInterface
{
    /**
     * Cache tag constant
     */
    const CACHE_TAG = 'ashokkumar_login_as_customer_log';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'ashokkumar_login_as_customer_log';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Ashokkumar\LoginAsCustomer\Model\ResourceModel\AuditLog::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getAdminId()
    {
        return $this->getData(self::ADMIN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdminId($adminId)
    {
        return $this->setData(self::ADMIN_ID, $adminId);
    }

    /**
     * @inheritDoc
     */
    public function getAdminUsername()
    {
        return $this->getData(self::ADMIN_USERNAME);
    }

    /**
     * @inheritDoc
     */
    public function setAdminUsername($username)
    {
        return $this->setData(self::ADMIN_USERNAME, $username);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerEmail($email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getTokenHash()
    {
        return $this->getData(self::TOKEN_HASH);
    }

    /**
     * @inheritDoc
     */
    public function setTokenHash($hash)
    {
        return $this->setData(self::TOKEN_HASH, $hash);
    }

    /**
     * @inheritDoc
     */
    public function getIpAddress()
    {
        return $this->getData(self::IP_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setIpAddress($ipAddress)
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt()
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * @inheritDoc
     */
    public function setExpiresAt($expiresAt)
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * @inheritDoc
     */
    public function getUsedAt()
    {
        return $this->getData(self::USED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUsedAt($usedAt)
    {
        return $this->setData(self::USED_AT, $usedAt);
    }
}
