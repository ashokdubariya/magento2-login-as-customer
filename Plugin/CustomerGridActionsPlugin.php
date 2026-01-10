<?php
/**
 * Customer Grid Actions Plugin
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Plugin;

use Magento\Customer\Ui\Component\Listing\Column\Actions as CustomerActions;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ashokkumar\LoginAsCustomer\Model\Config;

/**
 * Class CustomerGridActionsPlugin
 *
 * Adds "Login as Customer" action to customer grid with multi-website support
 */
class CustomerGridActionsPlugin
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Add "Login as Customer" action to grid with multi-website support
     *
     * @param CustomerActions $subject
     * @param array $result
     * @param array $dataSource
     * @return array
     */
    public function afterPrepareDataSource(
        CustomerActions $subject,
        array $result,
        array $dataSource
    ) {
        // Check if module is enabled and user has permission
        if (!$this->config->isEnabled() || 
            !$this->authorization->isAllowed('Ashokkumar_LoginAsCustomer::login_action')) {
            return $result;
        }

        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $customerId = (int)$item['entity_id'];
                    $customerWebsiteId = isset($item['website_id']) ? (int)$item['website_id'] : null;
                    
                    $websites = $this->getCustomerWebsites($customerWebsiteId);
                    
                    if (count($websites) === 1) {
                        // Single website - simple action
                        $websiteId = (int)array_key_first($websites);
                        $item[$subject->getData('name')]['login_as_customer'] = [
                            'href' => $this->urlBuilder->getUrl(
                                'loginascustomer/login/generate',
                                [
                                    'customer_id' => $customerId,
                                    'website_id' => $websiteId
                                ]
                            ),
                            'label' => __('Login as Customer'),
                            'target' => '_blank',
                            'hidden' => false,
                        ];
                    } elseif (count($websites) > 1) {
                        // Multiple websites - create submenu structure
                        $actions = [];
                        
                        foreach ($websites as $websiteId => $websiteName) {
                            $actions[] = [
                                'href' => $this->urlBuilder->getUrl(
                                    'loginascustomer/login/generate',
                                    [
                                        'customer_id' => $customerId,
                                        'website_id' => $websiteId
                                    ]
                                ),
                                'label' => __('Login as Customer') . ' (' . $websiteName . ')',
                                'target' => '_blank',
                            ];
                        }
                        
                        // Add all website-specific actions
                        $actionIndex = 0;
                        foreach ($actions as $action) {
                            $item[$subject->getData('name')]['login_as_customer_' . $actionIndex] = $action;
                            $actionIndex++;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get customer's associated websites
     *
     * @param int|null $customerWebsiteId
     * @return array [website_id => website_name]
     */
    private function getCustomerWebsites(?int $customerWebsiteId): array
    {
        $websites = [];
        
        try {
            // Get all websites
            $allWebsites = $this->storeManager->getWebsites(false, true);
            
            foreach ($allWebsites as $websiteCode => $website) {
                // Skip admin website
                if ($website->getId() == 0) {
                    continue;
                }
                
                // If customer sharing is global, show all websites
                // Otherwise, show only customer's assigned website
                if ($this->config->isWebsiteShareEnabled() || $website->getId() == $customerWebsiteId) {
                    $websites[$website->getId()] = $website->getName();
                }
            }
            
        } catch (\Exception $e) {
            // Error - return empty array
        }
        
        return $websites;
    }
}
