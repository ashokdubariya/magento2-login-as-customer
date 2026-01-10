<?php
/**
 * Login as Customer Button UI Component
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Ui\Customer\Component\Control;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ashokkumar\LoginAsCustomer\Model\Config;

/**
 * Login as Customer button with multi-website support
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param Escaper $escaper
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        Escaper $escaper,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $registry);
        $this->authorization = $context->getAuthorization();
        $this->config = $config;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $customerId = (int)$this->getCustomerId();
        $data = [];

        $isAllowed = $customerId && $this->authorization->isAllowed('Ashokkumar_LoginAsCustomer::login_action');
        $isEnabled = $this->config->isEnabled();

        if ($isAllowed && $isEnabled) {
            $websites = $this->getCustomerWebsites($customerId);
            
            if (count($websites) === 1) {
                // Single website - simple button
                $websiteId = (int)array_key_first($websites);
                $data = [
                    'label' => __('Login as Customer'),
                    'class' => 'login-as-customer',
                    'on_click' => sprintf(
                        "window.open('%s', '_blank')",
                        $this->escaper->escapeJs($this->getLoginUrl($customerId, $websiteId))
                    ),
                    'sort_order' => 60,
                ];
            } elseif (count($websites) > 1) {
                // Multiple websites - dropdown button
                $data = [
                    'label' => __('Login as Customer'),
                    'class' => 'login-as-customer',
                    'class_name' => 'Magento\Ui\Component\Control\SplitButton',
                    'options' => $this->getWebsiteOptions($customerId, $websites),
                    'sort_order' => 60,
                ];
            }
        }

        return $data;
    }

    /**
     * Get customer's associated websites
     *
     * @param int $customerId
     * @return array [website_id => website_name]
     */
    private function getCustomerWebsites(int $customerId): array
    {
        $websites = [];
        
        try {
            $customer = $this->customerRepository->getById($customerId);
            $customerWebsiteId = (int)$customer->getWebsiteId();
            
            // Get all websites
            $allWebsites = $this->storeManager->getWebsites(false, true);
            
            foreach ($allWebsites as $websiteCode => $website) {
                // Skip admin website
                if ($website->getId() == 0) {
                    continue;
                }
                
                // Include customer's primary website and check if customer can access other websites
                if ($website->getId() == $customerWebsiteId || $this->config->isWebsiteShareEnabled()) {
                    $websites[$website->getId()] = $website->getName();
                }
            }
            
        } catch (NoSuchEntityException | LocalizedException $e) {
            // Customer not found or error - return empty array
        }
        
        return $websites;
    }

    /**
     * Get website options for dropdown
     *
     * @param int $customerId
     * @param array $websites
     * @return array
     */
    private function getWebsiteOptions(int $customerId, array $websites): array
    {
        $options = [];
        
        foreach ($websites as $websiteId => $websiteName) {
            $options[] = [
                'label' => $websiteName,
                'onclick' => sprintf(
                    "window.open('%s', '_blank')",
                    $this->escaper->escapeJs($this->getLoginUrl($customerId, $websiteId))
                ),
            ];
        }
        
        return $options;
    }

    /**
     * Get login URL for specific website
     *
     * @param int $customerId
     * @param int $websiteId
     * @return string
     */
    private function getLoginUrl(int $customerId, int $websiteId): string
    {
        return $this->getUrl(
            'loginascustomer/login/generate',
            [
                'customer_id' => $customerId,
                'website_id' => $websiteId,
            ]
        );
    }
}
