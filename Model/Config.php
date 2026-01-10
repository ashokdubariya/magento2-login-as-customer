<?php
/**
 * Configuration Provider
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * Provides access to module configuration settings
 */
class Config
{
    /**
     * Configuration paths
     */
    private const XML_PATH_ENABLED = 'ashokkumar_loginascustomer/general/enabled';
    private const XML_PATH_TOKEN_LIFETIME = 'ashokkumar_loginascustomer/general/token_lifetime';
    private const XML_PATH_REDIRECT_PAGE = 'ashokkumar_loginascustomer/general/redirect_page';
    private const XML_PATH_AUDIT_ENABLED = 'ashokkumar_loginascustomer/general/audit_enabled';

    /**
     * Default values
     */
    private const DEFAULT_TOKEN_LIFETIME = 5; // minutes
    private const DEFAULT_REDIRECT_PAGE = 'customer/account';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get token lifetime in minutes
     *
     * @param int|null $storeId
     * @return int
     */
    public function getTokenLifetime(?int $storeId = null): int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_TOKEN_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value ? (int)$value : self::DEFAULT_TOKEN_LIFETIME;
    }

    /**
     * Get redirect page after login
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectPage(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value ?: self::DEFAULT_REDIRECT_PAGE;
    }

    /**
     * Check if audit logging is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAuditEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AUDIT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if customer accounts are shared between websites
     *
     * @return bool
     */
    public function isWebsiteShareEnabled(): bool
    {
        $shareScope = $this->scopeConfig->getValue(
            'customer/account_share/scope',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        
        // 0 = Global (shared across websites), 1 = Per Website
        return (int)$shareScope === 0;
    }
}
