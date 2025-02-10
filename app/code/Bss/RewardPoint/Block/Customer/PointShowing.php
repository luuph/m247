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
namespace Bss\RewardPoint\Block\Customer;

use Bss\RewardPoint\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Bss\RewardPoint\Model\Config\Source\Image;
use Bss\RewardPoint\Helper\RewardCustomAction;
use Magento\Framework\Registry;

/**
 * Class Point Showing
 *
 * Bss\RewardPoint\Block\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PointShowing extends \Bss\RewardPoint\Block\RewardPoint
{
    public const TYPE_ACTION_REGISTRATION = 'registration';
    public const TYPE_ACTION_SUBCRIBER = 'subcriber';
    public const TYPE_ACTION_REVIEW = 'review';
    public const TYPE_ACTION_PRODUCT = 'product';
    public const TYPE_ACTION_PRODUCT_IN_CATEGORY = 'category';

    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var RewardCustomAction
     */
    protected $rewardActionHelper;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * PointShowing constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Bss\RewardPoint\Model\Config\Source\TransactionActions $transactionActions
     * @param \Bss\RewardPoint\Helper\InjectModel $helperInject
     * @param RewardCustomAction $rewardActionHelper
     * @param Data $bssHelper
     * @param Registry $coreRegistry
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Bss\RewardPoint\Model\Config\Source\TransactionActions $transactionActions,
        \Bss\RewardPoint\Helper\InjectModel $helperInject,
        RewardCustomAction $rewardActionHelper,
        Data $bssHelper,
        Registry $coreRegistry,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {
        $this->bssHelper = $bssHelper;
        $this->rewardActionHelper = $rewardActionHelper;
        $this->coreRegistry = $coreRegistry;
        $this->subscriber = $subscriber;
        $this->assetRepo = $assetRepo;
        parent::__construct(
            $context,
            $customerSession,
            $storeManager,
            $priceHelper,
            $transactionActions,
            $helperInject,
            $data
        );
    }

    /**
     * Get point to show
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointToShow()
    {
        return (int) $this->getBalanceInfo()->getPointBalance();
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        if (empty($this->getCustomerId())) {
            return false;
        }
        return true;
    }

    /**
     * Get Point Icon
     *
     * @return string
     */
    protected function getPointIcon()
    {
        return $this->bssHelper->getValueConfig(Data::XML_PATH_POINT_ICON, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get url of point icon
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPointIconUrl()
    {
        $mediaDir = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if ($this->getPointIcon() == Image::DEFAULT_ICON_VALUE) {
            return $this->assetRepo->getUrl('Bss_RewardPoint::' . Image::DEFAULT_ICON_VALUE);
        }
        if (empty($this->getPointIcon())) {
            return '';
        }
        return $mediaDir . Image::UPLOAD_DIR . "/" . $this->getPointIcon();
    }

    /**
     * Check if show point in costomer account header
     *
     * @return bool
     */
    public function isShowPointInHeader()
    {
        $isShowingInHeader = $this->bssHelper->getFlagConfig(
            Data::XML_PATH_SW_POINT_HEADER,
            ScopeInterface::SCOPE_STORE
        );
        return $isShowingInHeader && $this->bssHelper->isActive();
    }

    /**
     * Get message for registration rule
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRegistrationPoint()
    {
        $point = $this->rewardActionHelper->getRegistrationRulePoint();
        if ($point > 0) {
            return __("Sign up now to earn %1 points", $point);
        }
        return '';
    }

    /**
     * Get message for subcriber rule
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSubcriberPoint()
    {
        $isLoggin = $this->customerLoggedIn();
        if ($isLoggin && $this->getCustomer()->getEmail()) {
            $customerGroupId = $this->getCustomerGroupId();
            $checkSubscriber = $this->subscriber->loadByEmail($this->getCustomer()->getEmail());
            $point = $this->rewardActionHelper->getSubcriberRulePoint($customerGroupId);
            $point = ($isLoggin && $checkSubscriber->isSubscribed()) ? 0 : $point;
            if ($point > 0) {
                return __("You can earn %1 points by subcribe newsletter", $point);
            }
        }
        return '';
    }

    /**
     * Get message for review rule
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getReviewPoint()
    {
        $customerGroupId = $this->getCustomerGroupId();
        $point = $this->rewardActionHelper->getReviewRulePoint(
            $customerGroupId,
            $this->coreRegistry->registry('current_product')
        );
        if ($point > 0) {
            if ($this->customerLoggedIn()) {
                return __("Earn %1 points for each review", $point);
            }
            return __("Login and earn %1 points for each review", $point);
        }
        return '';
    }

    /**
     * Get product point
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductPoint()
    {
        /**
         * Not show block add from plugin \Bss\RewardPoint\Plugin\Product\CustomizeProductListCommon for current product
         */
        if (!empty($this->coreRegistry->registry('current_product'))
            && $this->getMessageType() == self::TYPE_ACTION_PRODUCT_IN_CATEGORY) {
            if ($this->getProduct()) {
                $currentProduct = $this->coreRegistry->registry('current_product');
                if ($currentProduct->getId() == $this->getProduct()->getId()) {
                    return '';
                }
            }
        }
        if ($this->getProduct()) {
            $product  = $this->getProduct();
        } else {
            $product = $this->coreRegistry->registry('current_product');
        }

        /**
         * Not show message for grouped product in product page
         */
        if ($product->getTypeId() == 'grouped' && $this->getMessageType() == self::TYPE_ACTION_PRODUCT) {
            return '';
        }
        $customerGroupId = $this->getCustomerGroupId();
        $point = $this->rewardActionHelper->getPointByProduct($product, $customerGroupId);
        if ($point > 0) {
            return $this->getProductPointMessage($product, $point);
        }
        return '';
    }

    /**
     * Get message by customer group
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $point
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductPointMessage($product, $point)
    {
        $isGroupedOrConfigurable = $product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped';
        $addText = $isGroupedOrConfigurable ? 'atleast ' : '';
        $dependQty = $product->getResource()->getAttributeRawValue(
            $product->getId(),
            'dependent_qty',
            $this->storeManager->getStore()->getId()
        );
        if ($this->customerLoggedIn()) {
            if ($dependQty) {
                return __("Earn %1%2 points for 1 product item", $addText, $point);
            }
            if ($isGroupedOrConfigurable) {
                return __("Buy now to earn a minimum of %1 points", $point);
            }
            return __("Buy this product to earn %1%2 points", $addText, $point);
        } else {
            if ($dependQty) {
                return __("Login and Earn %1%2 points for 1 product item", $addText, $point);
            }
            if ($isGroupedOrConfigurable) {
                return __("Login and buy this product to earn a minimum of %1 points", $point);
            }
            return __("Login and Buy this product to earn %1%2 points", $addText, $point);
        }
    }

    /**
     * Get reward point message
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRewardPointMessage()
    {
        switch ($this->getMessageType()) {
            case self::TYPE_ACTION_REGISTRATION:
                $message = $this->getRegistrationPoint();
                break;
            case self::TYPE_ACTION_SUBCRIBER:
                $message = $this->getSubcriberPoint();
                break;
            case self::TYPE_ACTION_REVIEW:
                $message = $this->getReviewPoint();
                break;
            case self::TYPE_ACTION_PRODUCT:
                $message = $this->getProductPoint();
                break;
            case self::TYPE_ACTION_PRODUCT_IN_CATEGORY:
                $message = $this->getProductPoint();
                break;
            default:
                $message = '';
        }
        return $message;
    }
}
