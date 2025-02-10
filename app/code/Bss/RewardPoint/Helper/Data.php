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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Helper;

use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Directory\Model\CurrencyFactory;

/**
 * Class data
 *
 * Bss\RewardPoint\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_ACTIVE = 'bssrewardpoint/general/active';

    public const XML_PATH_MAXIMUM_THRESHOLD = 'bssrewardpoint/general/maximum_threshold';

    public const XML_PATH_REDEEM_THRESHOLD = 'bssrewardpoint/general/redeem_threshold';

    public const XML_PATH_EXPIRE_DAY = 'bssrewardpoint/general/expire_day';

    public const XML_PATH_EARN_TAX = 'bssrewardpoint/earning_point/earn_tax';

    public const XML_PATH_EARN_SHIPPING = 'bssrewardpoint/earning_point/earn_shipping';

    public const XML_PATH_EARN_ORDER_PAID = 'bssrewardpoint/earning_point/earn_order_paid';

    public const XML_PATH_MAXIMUM_EARN_ORDER = 'bssrewardpoint/earning_point/maximum_earn_order';

    public const XML_PATH_MAXIMUM_EARN_REVIEW = 'bssrewardpoint/earning_point/maximum_earn_review';

    public const XML_PATH_AUTO_REFUND = 'bssrewardpoint/earning_point/auto_refund';

    public const XML_PATH_MAXIMUM_POINT_ORDER = 'bssrewardpoint/spending_point/maximum_point_order';

    public const XML_PATH_ALLOW_SPEND_TAX = 'bssrewardpoint/spending_point/allow_spend_tax';

    public const XML_PATH_ALLOW_SPEND_SHIPPING = 'bssrewardpoint/spending_point/allow_spend_shipping';

    public const XML_PATH_RESTORE_SPENT = 'bssrewardpoint/spending_point/restore_spent';

    public const XML_PATH_POINT_ICON = 'bssrewardpoint/frontend/point_icon';

    public const XML_PATH_SW_POINT_HEADER = 'bssrewardpoint/frontend/sw_point_header';

    public const XML_PATH_POINT_MESS_REGISTER = 'bssrewardpoint/frontend/point_mess_register';

    public const XML_PATH_POINT_SUBSCRIBLE = 'bssrewardpoint/frontend/point_subscrible';

    public const XML_PATH_CART_ORDER_SUMMARY = 'bssrewardpoint/frontend/cart_order_summary';

    public const XML_PATH_PRODUCT_PAGE_TAB_REVIEW = 'bssrewardpoint/frontend/product_page_tab_review';

    public const XML_PATH_PRODUCT_PAGE_REWARD_POINT = 'bssrewardpoint/frontend/product_page_reward_point';

    public const XML_PATH_CATE_PAGE_REWARD_POINT = 'bssrewardpoint/frontend/cate_page_reward_point';

    public const XML_PATH_POINT_SLIDER = 'bssrewardpoint/frontend/point_slider';

    public const XML_PATH_SENDER = 'bssrewardpoint/email_notification/sender';

    public const XML_PATH_EARN_POINT_TEMPLATE = 'bssrewardpoint/email_notification/earn_point_template';

    public const XML_PATH_SPEND_POINT_TEMPLATE = 'bssrewardpoint/email_notification/spend_point_template';

    public const XML_PATH_EXPIRY_WARNING_TEMPLATE = 'bssrewardpoint/email_notification/expiry_warning_template';

    public const XML_PATH_EXPIRE_DAY_BEFORE = 'bssrewardpoint/email_notification/expire_day_before';

    public const XML_PATH_SUBSCRIBLE = 'bssrewardpoint/email_notification/subscrible';

    public const XML_PATH_DATE_FIELD_ORDER = 'catalog/custom_options/date_fields_order';

    public const XML_PATH_FIRST_DAY_WEEK = 'general/locale/firstday';

    /**
     * Date time formatter
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\TransactionActions
     */
    protected $transactionActions;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerJson;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param TransactionActions $transactionActions
     * @param Json $serializerJson
     * @param ProductMetadataInterface $productMetadata
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyInterface $currency
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context                                                 $context,
        \Magento\Framework\Stdlib\DateTime                      $dateTime,
        \Magento\Store\Model\StoreManagerInterface              $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder       $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface      $inlineTranslation,
        \Bss\RewardPoint\Model\Config\Source\TransactionActions $transactionActions,
        \Magento\Framework\Serialize\Serializer\Json            $serializerJson,
        \Magento\Framework\App\ProductMetadataInterface         $productMetadata,
        PriceCurrencyInterface                                  $priceCurrency,
        CurrencyInterface                                       $currency,
        CurrencyFactory                                         $currencyFactory
    ) {
        $this->dateTime = $dateTime;
        $this->serializerJson = $serializerJson;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->transactionActions = $transactionActions;
        $this->productMetadata = $productMetadata;
        $this->priceCurrency = $priceCurrency;
        $this->currency = $currency;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context);
    }

    /**
     * Get flag config
     *
     * @param string $path
     * @param string $scope
     * @param int $id
     * @return bool
     */
    public function getFlagConfig($path, $scope, $id = null)
    {
        return $this->scopeConfig->isSetFlag($path, $scope, $id);
    }

    /**
     * Get value config
     *
     * @param string $path
     * @param string $scope
     * @param int $id
     * @return string
     */
    public function getValueConfig($path, $scope, $id = null)
    {
        return $this->scopeConfig->getValue($path, $scope, $id);
    }

    /**
     * Is active
     *
     * @param int $id
     * @return bool
     */
    public function isActive($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get points maximum
     *
     * @param int $id
     * @return string
     */
    public function getPointsMaximum($id = null)
    {
        return $this->getValueConfig(self::XML_PATH_MAXIMUM_THRESHOLD, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get points threshold
     *
     * @param int $id
     * @return string
     */
    public function getPointsThreshold($id = null)
    {
        return $this->getValueConfig(self::XML_PATH_REDEEM_THRESHOLD, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get expire day
     *
     * @param int $id
     * @return bool|float|int|string|null
     */
    public function getExpireDay($id = null)
    {
        $lifetime = (int)$this->getValueConfig(self::XML_PATH_EXPIRE_DAY, ScopeInterface::SCOPE_WEBSITE, $id);
        if ($lifetime > 0) {
            return $lifetime;
        }
        return false;
    }

    /**
     * Convert expired day to date
     *
     * @param int $limitDays
     * @param string $createdAt
     * @return bool|false|float|int|string|null
     */
    public function convertExpiredDayToDate($limitDays, $createdAt)
    {
        if ($limitDays > 0) {
            $expires = strtotime($createdAt) + $limitDays * 86400;
            // format follow magento
            $expires = $this->dateTime->formatDate($expires);
            return $expires;
        }
        return false;
    }

    /**
     * Is earn point for tax
     *
     * @param int $id
     * @return bool
     */
    public function isEarnPointforTax($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_EARN_TAX, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Is earn point for ship
     *
     * @param int $id
     * @return bool
     */
    public function isEarnPointforShip($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_EARN_SHIPPING, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Is earn order paid by point
     *
     * @param int $id
     * @return bool
     */
    public function isEarnOrderPaidbyPoint($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_EARN_ORDER_PAID, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get max earn per order
     *
     * @param int $id
     * @return string
     */
    public function getMaximumEarnPerOrder($id = null)
    {
        return $this->getValueConfig(self::XML_PATH_MAXIMUM_EARN_ORDER, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get max earn review
     *
     * @param int $id
     * @return array|bool|string
     */
    public function getMaximumEarnReview($id = null)
    {
        $result = $this->getValueConfig(self::XML_PATH_MAXIMUM_EARN_REVIEW, ScopeInterface::SCOPE_WEBSITE, $id);
        if ($result) {
            $maximum = $this->serializerJson->unserialize($result);
            $type_date = $maximum['type_date'];
            $period_time = (int)$maximum['period_time'];
            $maximum_point = (int)$maximum['maximum_point_review'];
            if ($period_time > 0 && $maximum_point > 0) {
                $today = $this->getCreateAt();
                $to = $this->dateTime->formatDate(strtotime($today . ' +1 day'), false);
                switch ($type_date) {
                    case 'day':
                        $from = $this->dateTime->formatDate(strtotime($today . ' -1 day'), false);
                        break;
                    case 'month':
                        $from = $this->dateTime->formatDate(strtotime($today . ' -1 month'), false);
                        break;

                    default:
                        $from = $this->dateTime->formatDate(strtotime($today . ' -1 year'), false);
                        break;
                }
                $result = [
                    'from' => $from,
                    'to' => $to,
                    'maximum_point' => $maximum_point
                ];
            } else {
                return false;
            }
        }
        return $result;
    }

    /**
     * Get all website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getAllWebsites()
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * Is auto refund
     *
     * @param int $id
     * @return bool
     */
    public function isAutoRefundOrderToPoints($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_AUTO_REFUND, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get max point can spend per order
     *
     * @param int $id
     * @return string
     */
    public function getMaximumPointCanSpendPerOrder($id = null)
    {
        return $this->getValueConfig(self::XML_PATH_MAXIMUM_POINT_ORDER, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Is spend point for tax
     *
     * @param int $id
     * @return bool
     */
    public function isSpendPointforTax($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_ALLOW_SPEND_TAX, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Is spend point for ship
     *
     * @param int $id
     * @return bool
     */
    public function isSpendPointforShip($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_ALLOW_SPEND_SHIPPING, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Is point slider
     *
     * @param int $id
     * @return bool
     */
    public function isPointSlider($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_POINT_SLIDER, ScopeInterface::SCOPE_STORE, $id);
    }

    /**
     * Is restore spent
     *
     * @param int $id
     * @return bool
     */
    public function isRestoreSpent($id = null)
    {
        return $this->getFlagConfig(self::XML_PATH_RESTORE_SPENT, ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get create at
     *
     * @return string|null
     */
    public function getCreateAt()
    {
        // time sever
        $now = time();
        return $this->dateTime->formatDate($now);
    }

    /**
     * Get base currency code
     *
     * @param int $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseCurrencyCode($websiteId = null)
    {
        if (!$websiteId) {
            return $this->storeManager->getStore()->getBaseCurrencyCode();
        }
        return $this->storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
    }

    /**
     * Send email for each action reward point
     *
     * @param \Bss\RewardPoint\Model\Transaction $subject
     * @param string $pointBalance
     * @param array $customerInfo
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendNotiEmail($subject, $pointBalance, $customerInfo, $rate)
    {
        $store = $this->getStoreToSendMailPerAction($customerInfo, $subject);
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $store->getId()
        ];
        $this->inlineTranslation->suspend();
        $transactionOption = $this->transactionActions->toArray();

        $templateVars = [
            'store' => $store,
            'transaction' => $subject,
            'action_name' => $transactionOption[$subject->getAction()],
            'customer_name' => $customerInfo['name'],
            'expire_date' => $subject->getExpiresAt()
                ? date("Y/m/d", strtotime($subject->getExpiresAt())) : 'indefinite',
            'balance' => $pointBalance,
            'x_point' => $rate->getBasecurrencyToPointRate() ? ceil($rate->getBasecurrencyToPointRate()) : 1,
            'base_currency_code' => $rate->getBaseCurrrencyCode() ?? ''
        ];

        $templateId = $this->getValueConfig(
            self::XML_PATH_EARN_POINT_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        if ($subject->getPoint() < 0) {
            $templateId = $this->getValueConfig(
                self::XML_PATH_SPEND_POINT_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFromByScope($this->getValueConfig(
                self::XML_PATH_SENDER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            ), $store->getId())
            ->addTo($customerInfo['mail'], $customerInfo['name'])
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get store id to send email
     *
     * @param array $customerInfo
     * @param \Bss\RewardPoint\Model\Transaction $subject
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreToSendMailPerAction($customerInfo, $subject)
    {
        $sendMailByCustomerStore = [
            TransactionActions::ADMIN_CHANGE,
            TransactionActions::REGISTRATION,
            TransactionActions::BIRTHDAY,
            TransactionActions::IMPORT
        ];
        if (in_array($subject->getAction(), $sendMailByCustomerStore)) {
            return $this->storeManager->getStore($customerInfo['store_id']);
        }
        return $this->storeManager->getStore();
    }

    /**
     * Send email for expires transaction
     *
     * @param array $expiresInfo
     * @param array $customerInfo
     * @param int $pointBalance
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendExpiresEmail($expiresInfo, $customerInfo, $pointBalance)
    {
        if (isset($expiresInfo['store_id'])) {
            $store_id = $expiresInfo['store_id'];
        } else {
            $store_id = $this->storeManager->getStore()->getId();
        }

        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $store_id
        ];
        $this->inlineTranslation->suspend();

        $createdAt = isset($expiresInfo['created_at']) ? $expiresInfo['created_at'] : false;
        $limitDays = isset($expiresInfo['expires_at']) ? $expiresInfo['expires_at'] : false;
        $expiredAt = $createdAt && $limitDays ? $this->convertExpiredDayToDate($limitDays, $createdAt) : '';

        $templateVars = [
            'store' => $this->storeManager->getStore($store_id),
            'customer_name' => $customerInfo['name'],
            'balance' => $pointBalance,
            'date' => $expiredAt,
            'point' => $expiresInfo['point_balance']
        ];
        $templateId = $this->getValueConfig(
            self::XML_PATH_EXPIRY_WARNING_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFromByScope($this->getValueConfig(
                self::XML_PATH_SENDER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ), $store_id)
            ->addTo($customerInfo['mail'], $customerInfo['name'])
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get order of date
     *
     * @return string
     */
    public function getDateOrder()
    {
        return $this->getValueConfig(self::XML_PATH_DATE_FIELD_ORDER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get first day of week
     *
     * @return string
     */
    public function getFirstDayOfWeek()
    {
        return $this->getValueConfig(self::XML_PATH_FIRST_DAY_WEEK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check magento version
     *
     * @return bool
     */
    public function checkMagentoHigherV244()
    {
        return version_compare($this->productMetadata->getVersion(), '2.4.4', '>=');
    }

    /**
     * Get all allowed currency
     *
     * @return array
     * @throws CurrencyException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllowedCurrencies()
    {
        $baseCurrency = $this->getBaseCurrencyCode();
        $currencyModel = $this->currencyFactory->create();
        $currenciesCode = $currencyModel->getCurrencyRates($baseCurrency, $currencyModel->getConfigAllowCurrencies());
        $listCurrency[$baseCurrency] = $this->getCurrencyName($baseCurrency);
        foreach ($currenciesCode as $currency => $rate) {
            if ($currency != $baseCurrency) {
                $listCurrency[$currency] = $this->getCurrencyName($currency);
            }
        }
        return $listCurrency;
    }

    /**
     * Get currency name by currency code
     *
     * @param string $currencyCode
     * @return string|null
     * @throws CurrencyException
     * @throws LocalizedException
     */
    public function getCurrencyName($currencyCode)
    {
        if (!$currencyCode) {
            $currencyCode = $this->getBaseCurrencyCode();
        }
        return $this->currency->getCurrency($currencyCode)->getName();
    }

    /**
     * Rate currency
     *
     * @param string $currency
     * @return float
     */
    public function rateCurrency($currency)
    {
        return $this->priceCurrency->convert(1, null, $currency);
    }

    /**
     * Convert amount
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     * @throws NoSuchEntityException
     */
    public function convertAmountBaseCurrency($amount, $fromCurrency, $toCurrency = null)
    {
        $toCurrency = $toCurrency ?? $this->getBaseCurrencyCode();
        if (!$fromCurrency || strcmp($fromCurrency, $toCurrency) == 0) {
            return $amount;
        }
        return round($amount * ($this->rateCurrency($toCurrency) / $this->rateCurrency($fromCurrency)), 2);
    }
}
