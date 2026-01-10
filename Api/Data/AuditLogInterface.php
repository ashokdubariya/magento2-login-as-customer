<?php
/**
 * Audit Log Data Interface
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Api\Data;

/**
 * Interface AuditLogInterface
 * @api
 */
interface AuditLogInterface
{
    const ENTITY_ID = 'entity_id';
    const ADMIN_ID = 'admin_id';
    const ADMIN_USERNAME = 'admin_username';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const TOKEN_HASH = 'token_hash';
    const IP_ADDRESS = 'ip_address';
    const STATUS = 'status';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const EXPIRES_AT = 'expires_at';
    const USED_AT = 'used_at';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get admin user ID
     *
     * @return int
     */
    public function getAdminId();

    /**
     * Set admin user ID
     *
     * @param int $adminId
     * @return $this
     */
    public function setAdminId($adminId);

    /**
     * Get admin username
     *
     * @return string
     */
    public function getAdminUsername();

    /**
     * Set admin username
     *
     * @param string $username
     * @return $this
     */
    public function setAdminUsername($username);

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer email
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @param string $email
     * @return $this
     */
    public function setCustomerEmail($email);

    /**
     * Get token hash
     *
     * @return string
     */
    public function getTokenHash();

    /**
     * Set token hash
     *
     * @param string $hash
     * @return $this
     */
    public function setTokenHash($hash);

    /**
     * Get IP address
     *
     * @return string|null
     */
    public function getIpAddress();

    /**
     * Set IP address
     *
     * @param string|null $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at timestamp
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get expires at timestamp
     *
     * @return string
     */
    public function getExpiresAt();

    /**
     * Set expires at timestamp
     *
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt);

    /**
     * Get used at timestamp
     *
     * @return string|null
     */
    public function getUsedAt();

    /**
     * Set used at timestamp
     *
     * @param string|null $usedAt
     * @return $this
     */
    public function setUsedAt($usedAt);
}
