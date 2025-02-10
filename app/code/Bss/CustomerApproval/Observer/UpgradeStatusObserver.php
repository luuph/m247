<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlInterface;
use Bss\CustomerApproval\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Bss\CustomerApproval\Helper\Email;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeStatusObserver implements ObserverInterface
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\CustomerApproval\Helper\Email
     */
    protected $emailHelper;

    /**
     * UpgradeStatusObserver constructor.
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $url
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param Email $emailHelper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerFactory $customerFactory,
        UrlInterface $url,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        Email $emailHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerFactory = $customerFactory;
        $this->url = $url;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $customer = $observer->getCustomer();
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
            $customerFirstName = $customer->getFirstName();
            $customerLastName = $customer->getLastName();
            $customerName = $customerFirstName. " " .$customerLastName;
            $storeId = $customer->getStoreId();
            if ($this->helper->isEnableAdminEmail() && $this->helper->getAdminEmail()) {
                $emailTemplate = $this->helper->getAdminEmailTemplate();
                $this->sendEmail($customerEmail, $emailTemplate);
            }
            if ($this->helper->isAutoApproval()) {
                $value = $this->helper->getApproveValue();
            } else {
                if ($this->helper->isEnableCustomerEmail()) {
                    $emailTemplate = $this->helper->getCustomerRegistrationSuccessEmailTemplates($storeId);
                    $this->emailHelper->sendEmail($customerEmail, $emailTemplate, $customerName, $storeId);
                }
                $value = $this->helper->getPendingValue();
            }

            $customer = $this->customerFactory->create()->load($customerId);
            $customerDataModel = $customer->getDataModel();
            $customerDataModel->setCustomAttribute("activasion_status", $value);
            $customer->updateData($customerDataModel);
            $customer->save();
        }
    }

    /**
     * @param string $customerEmail
     * @param string $emailTemplate
     * @return void
     */
    protected function sendEmail($customerEmail, $emailTemplate)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $recipients = $this->helper->getAdminEmail();
            $recipients = str_replace(' ', '', $recipients);
            $recipients = (explode(',', $recipients));
            $email = $this->helper->getAdminEmailSender();
            $emailValue = 'trans_email/ident_'.$email.'/email';
            $emailName = 'trans_email/ident_'.$email.'/name';
            $emailSender = $this->scopeConfig->getValue($emailValue, ScopeInterface::SCOPE_STORE);
            $emailNameSender = $this->scopeConfig->getValue($emailName, ScopeInterface::SCOPE_STORE);
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($emailNameSender),
                'email' => $this->escaper->escapeHtml($emailSender),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars([
                    'varEmail'  => $customerEmail,
                ])
                ->setFrom($sender);
            foreach ($recipients as $email) {
                $transport->addTo($email);
            }
            $transport->getTransport()->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            //do nothing
        }
    }
}

