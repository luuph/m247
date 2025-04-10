<?php
namespace Mageplaza\Affiliate\Cron;

use DateTime;
use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Affiliate\Helper\Data;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Test
 * @package Mageplaza\Affiliate\Cron
 */
class SendMailCampaign
{
    const XML_PATH_EMAIL_NEW_CAMPAIGN_TEMPLATE     = 'affiliate/email/campaign_email/new_campaign_template';
    const XML_PATH_EMAIL_SENDER                    = 'affiliate/email/sender';
    const XML_PATH_EMAIL_EXPIRED_CAMPAIGN_TEMPLATE = 'affiliate/email/campaign_email/expired_campaign_template';
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var CampaignFactory
     */
    protected $_campaignFactory;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var AccountFactory
     */
    protected $_accountFactory;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * URL builder
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param LoggerInterface $logger
     * @param Data $dataHelper
     * @param CampaignFactory $campaignFactory
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param AccountFactory $accountFactory
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        Data $dataHelper,
        CampaignFactory $campaignFactory,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        AccountFactory $accountFactory,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder
    ) {
        $this->logger           = $logger;
        $this->_dataHelper      = $dataHelper;
        $this->_campaignFactory = $campaignFactory;
        $this->customerSession  = $customerSession;
        $this->storeManager     = $storeManager;
        $this->_accountFactory  = $accountFactory;
        $this->customerFactory  = $customerFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->_dataHelper->isEnabled()) {
            return;
        }
        $templateEmailNewCampaign     = self::XML_PATH_EMAIL_NEW_CAMPAIGN_TEMPLATE;
        $templateEmailExpiredCampaign = self::XML_PATH_EMAIL_EXPIRED_CAMPAIGN_TEMPLATE;
        $today = date('Y-m-d');
        $todayStart = $today . ' 00:00:00';
        $yesterdayStart = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
        if ($this->_dataHelper->getModuleConfig('email/campaign_email/new_campaign')) {
            $accountsReceiveCampaign = $this->_accountFactory->create()->getCollection()->addFieldToFilter('email_subcription',
                [
                    ['like' => '%"new_campaign":true%'],
                    ['like' => '']
                ]);

            if ($accountsReceiveCampaign->getSize()) {
                foreach ($accountsReceiveCampaign as $account) {
                    $affiliateGroupId = $account->getGroupId() ?: null;
                    $customer = $this->customerFactory->create()->load($account->getCustomerId());
                    $campaignModel = $this->_campaignFactory->create()->getCollection()->getAvailableCampaignForMail($affiliateGroupId);
                    $campaignModel->getSelect()->where('created_at >= ?', $yesterdayStart)
                        ->where('created_at < ?', $todayStart);
                    if ($campaignModel->getSize()) {
                        foreach ($campaignModel as $campain) {
                            try {
                                $templateParams = [
                                    'campaign_name'       => $campain->getName(),
                                    'description'         => $campain->getDescription(),
                                    'commissions'         => $this->formatCommission($campain->getCommission()),
                                    'discount_policy'     => $this->formatDiscountPolicy($campain->getDiscountAction(),
                                        $campain->getDiscountAmount()),
                                    'valid_date'          => $this->formatValidDate($campain->getFromDate(),
                                        $campain->getToDate()),
                                    'home_affiliate_link' => $this->_urlBuilder->setScope($customer->getStoreId())->getUrl('affiliate'),
                                    'name' => $customer->getFirstname(). ' ' . $customer->getLastname()

                                ];
                                $this->_dataHelper->sendEmailTemplate($customer, $templateEmailNewCampaign,
                                    $templateParams,
                                    self::XML_PATH_EMAIL_SENDER, null,  $customer->getEmail() );
                            } catch (Exception $e) {
                                $this->logger->debug($e->getMessage());
                            }
                        }
                    }

                }

            }
        }

        if ($this->_dataHelper->getModuleConfig('email/campaign_email/expired_campaign')) {
            $accountsReceiveExpired = $this->_accountFactory->create()->getCollection()->addFieldToFilter('email_subcription',
                [
                    ['like' => '%"expired_campaign":true%'],
                    ['like' => '']
                ]);
            if ($accountsReceiveExpired->getSize()) {
                foreach ($accountsReceiveExpired as $account) {
                    $affiliateGroupId = $account->getGroupId() ?: null;
                    $customer = $this->customerFactory->create()->load($account->getCustomerId());
                    $campaignModelExpired = $this->_campaignFactory->create()->getCollection()->getAvailableCampaignForMail($affiliateGroupId);
                    $campaignModelExpired->getSelect()->where('to_date >= ?', $yesterdayStart)
                        ->where('to_date < ?', $todayStart);
                    if ($campaignModelExpired->getSize()) {
                        foreach ($campaignModelExpired as $campain) {
                            try {
                                $templateParams = [
                                    'campaign_name'       => $campain->getName(),
                                    'home_affiliate_link' => $this->_urlBuilder->setScope($customer->getStoreId())->getUrl('affiliate'),
                                    'name' => $customer->getFirstname(). ' ' . $customer->getLastname()
                                ];
                                $this->_dataHelper->sendEmailTemplate($customer, $templateEmailExpiredCampaign, $templateParams,
                                    self::XML_PATH_EMAIL_SENDER, null, $customer->getEmail());
                            } catch (Exception $e) {
                                $this->logger->debug($e->getMessage());
                            }
                        }
                    }
                }

            }

        }
    }

    /**
     * @param $commission
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatCommission($commission)
    {
        $listContent = [];
        if ($commission) {
            foreach (json_decode($commission) as $obj) {
                if($obj->value || $obj->value_second) {
                    array_push($listContent,  '<b>' . $obj->name . '</b>');

                }
                if($obj->value) {
                    if($obj->type  === '3') {
                        array_push($listContent,  '<li>'. $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol() . round($obj->value ,2)  . ' for each item on first order' . '</li>');
                    } else {
                        array_push($listContent,  '<li>'. round($obj->value,2) . '%' . ' for each item on first order' . '</li>');
                    }
                }
                if($obj->value_second) {
                    if($obj->type_second      === '3') {
                        array_push($listContent, '<li>'. $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol() . round($obj->value_second ,2) . ' for each item on next order' . '</li>');
                    } else {
                        array_push($listContent, '<li>'. round($obj->value_second,2) . '%' . ' for each item on next order' . '</li>');
                    }
                }
            }
        }
        $htmlString = '<div>';
        foreach ($listContent as $element) {
            $htmlString .=  ($element) ;
        }
        $htmlString .= '</div>';

        return $htmlString;
    }

    /**
     * @param $action
     * @param $amount
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatDiscountPolicy($action, $amount)
    {
        if ((float) $amount !== 0) {
            if ($action === 'cart_fixed') {
                return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol() . round($amount, 2) . ' discount for for each item in the cart';
            } else {
                return round($amount, 2) . '%';
            }
        }
        return '';
    }

    /**
     * @param $from
     * @param $to
     *
     * @return string
     * @throws Exception
     */
    public static function formatValidDate($from, $to)
    {
        if (!$from && !$to) {
            return 'No limit';
        } else {
            $validDate = '';
            if ($from) {
                $fromDate  = (new DateTime($from));
                $validDate = $validDate . $fromDate->format("M d, Y");
            }
            if ($to && $from) {
                $toDate    = (new DateTime($to));
                $validDate = $validDate . ' - ' . $toDate->format("M d, Y");
            }
            if ($to && !$from) {
                $toDate    = (new DateTime($to));
                $validDate = $validDate . $toDate->format("M d, Y");
            }
            return $validDate;
        }
    }
}