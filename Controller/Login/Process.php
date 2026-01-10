<?php
/**
 * Frontend Controller - Process Customer Login
 * 
 * @category Ashokkumar
 * @package Ashokkumar_LoginAsCustomer
 */

declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Controller\Login;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Ashokkumar\LoginAsCustomer\Model\Config;
use Ashokkumar\LoginAsCustomer\Service\CustomerLoginService;
use Ashokkumar\LoginAsCustomer\Service\TokenService;
use Psr\Log\LoggerInterface;

class Process extends Action
{
    private $tokenService;
    private $customerLoginService;
    private $config;
    private $logger;

    public function __construct(
        Context $context,
        TokenService $tokenService,
        CustomerLoginService $customerLoginService,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->tokenService = $tokenService;
        $this->customerLoginService = $customerLoginService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->config->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Login as Customer feature is disabled.'));
            return $resultRedirect->setPath('/');
        }

        $token = $this->getRequest()->getParam('token');
        if (!$token) {
            $this->messageManager->addErrorMessage(__('Invalid login request.'));
            return $resultRedirect->setPath('/');
        }

        try {
            $auditLog = $this->tokenService->validateToken($token);

            if (!$auditLog) {
                $this->messageManager->addErrorMessage(
                    __('Login link has expired or is invalid.')
                );
                return $resultRedirect->setPath('/');
            }

            $customerId = (int) $auditLog->getCustomerId();
            $this->customerLoginService->loginCustomerById($customerId);

            $this->tokenService->markTokenAsUsed($auditLog);

            $this->messageManager->addSuccessMessage(
                __('You have been logged in successfully.')
            );

            $this->logger->info('Customer logged in via Login as Customer', [
                'customer_id' => $customerId,
                'admin_id' => $auditLog->getAdminId(),
                'audit_log_id' => $auditLog->getEntityId()
            ]);

            return $resultRedirect->setPath(
                $this->config->getRedirectPage()
            );

        } catch (\Exception $e) {
            $this->logger->error('Login as Customer error', ['exception' => $e]);

            if (isset($auditLog)) {
                $this->tokenService->markTokenAsFailed($auditLog);
            }

            $this->messageManager->addErrorMessage(
                __('An error occurred while logging you in.')
            );

            return $resultRedirect->setPath('/');
        }
    }
}
