<?php
/**
 * Admin Controller - Generate Login Token
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */

declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Controller\Adminhtml\Login;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Ashokkumar\LoginAsCustomer\Model\Config;
use Ashokkumar\LoginAsCustomer\Service\TokenService;

class Generate extends Action
{
    /**
     * ACL resource
     */
    const ADMIN_RESOURCE = 'Ashokkumar_LoginAsCustomer::login_action';

    private $tokenService;
    private $config;
    private $storeManager;

    public function __construct(
        Context $context,
        TokenService $tokenService,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->tokenService = $tokenService;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->config->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Login as Customer feature is disabled.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerId = (int) $this->getRequest()->getParam('customer_id');
        if (!$customerId) {
            $this->messageManager->addErrorMessage(__('Customer ID is required.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $websiteId = (int) $this->getRequest()->getParam('website_id');

        try {
            $tokenData = $this->tokenService->generateToken($customerId);

            // Get store based on website_id if provided
            if ($websiteId) {
                $website = $this->storeManager->getWebsite($websiteId);
                $store = $website->getDefaultStore();
            } else {
                $store = $this->storeManager->getStore();
            }

            $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

            $loginUrl = $baseUrl
                . 'loginascustomer/login/process?token='
                . $tokenData['token'];

            /*$this->messageManager->addSuccessMessage(
                __('Redirecting to customer account. Token expires in %1 minutes.',
                    $this->config->getTokenLifetime()
                )
            );*/

            return $resultRedirect->setUrl($loginUrl);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while generating login token.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)
                ->critical($e);
        }

        return $resultRedirect->setPath(
            'customer/index/edit',
            ['id' => $customerId]
        );
    }
}
