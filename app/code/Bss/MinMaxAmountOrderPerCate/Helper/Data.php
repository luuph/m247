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
 * @package    Bss_MinMaxAmountOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxAmountOrderPerCate\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Serialize\Serializer|\Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Data constructor.
     * @param Context $context
     * @param Http $request
     * @param Cart $cart
     * @param ManagerInterface $messageManager
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Framework\Registry $registry
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        Http $request,
        Cart $cart,
        ManagerInterface $messageManager,
        CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->request = $request;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->categoryFactory = $categoryFactory;
        $this->productMetadata = $productMetadata;
        $this->registry = $registry;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get Config
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->scopeConfig->getValue(
            'minmaxamountpercate/bssmmamountpc/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $categories
     * @param $customerGroupId
     * @param $extremum
     * @return array
     */
    public function validateMinMax($categories, $customerGroupId, $extremum)
    {
        $minMaxAmountConfig = $this->getConfig('min_max_amount');
        if ($minMaxAmountConfig) {
            $extrema = $this->json->unserialize($minMaxAmountConfig);
            $categoriesId = array_keys($categories);
            $result = [];
            if (!empty($extrema)) {
                foreach ($extrema as $value) {
                    if ($value['customer_group_id'] == $customerGroupId &&
                        in_array($value['category_id'], $categoriesId)) {
                        $amount = $categories[$value['category_id']];
                        if ($extremum == 'min' && $amount < (float)$value['min_sale_amount']) {
                            $result[$value['category_id']] = $value['min_sale_amount'];
                        }
                        if ($value['max_sale_amount'] && $extremum == 'max'
                            && $amount > (float)$value['max_sale_amount']) {
                            $result[$value['category_id']] = $value['max_sale_amount'];
                        }
                    }
                }
            }
            return $result;
        }
        return [];
    }

    /**
     * @param $categories
     * @param $customerGroupId
     * @return array
     */
    public function validate($categories, $customerGroupId)
    {
        $result['min'] = $this->validateMinMax($categories, $customerGroupId, 'min');
        $result['max'] = $this->validateMinMax($categories, $customerGroupId, 'max');
        return $result;
    }

    /**
     * @param $amount_categories
     * @param $items_name
     * @param $customerGroupId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateMinMaxAmount($amount_categories, $items_name, $customerGroupId)
    {
        $validationAmount = $this->validate($amount_categories, $customerGroupId);
        if (!empty($validationAmount['min']) || !empty($validationAmount['max'])) {
            if ($this->request->getFullActionName() === 'checkout_cart_index'
                || $this->request->getFullActionName() === 'multishipping_checkout_overviewPost'
            ) {
                foreach ($validationAmount as $extremum => $amount_limit) {
                    $this->showMessage($items_name, $amount_limit, $extremum);
                }
            }
            $this->cart->getQuote()->setHasError(true);
        }
    }

    /**
     * @param $items_name
     * @param $limit
     * @param $extremum
     */
    protected function showMessage($items_name, $limit, $extremum)
    {
        $mess_config = $this->getConfig('mess_err_max');
        if ($extremum == 'min') {
            $mess_config = $this->getConfig('mess_err_min');
        }
        $currencySymbol = $this->priceCurrency->getCurrencySymbol();
        foreach ($limit as $categoryId => $amount) {
            $product_names = [];
            foreach ($items_name as $item_name => $categoriesId) {
                if (in_array($categoryId, $categoriesId)) {
                    $product_names[] = $item_name;
                }
            }
            $productName = implode(',', $product_names);
            $cateName = $this->loadCateId($categoryId);
            $message = str_replace("{{category_name}}", $cateName, $mess_config);
            $message = str_replace("{{amount_limit}}", $currencySymbol."".$amount, $message);
            $message = str_replace("{{product_name}}", $productName, $message);
            //through message with multi shipping
            if ($this->request->getFullActionName() === 'multishipping_checkout_overviewPost') {
                $this->registry->register('bss_message', $message);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($message)
                );
            }
            $this->messageManager->addErrorMessage($message);
        }
    }

    /**
     * @param $categoryId
     * @return string
     */
    protected function loadCateId($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId)->getName();
    }

    /**
     * @param $ver
     * @param string $operator
     * @return bool|int
     */
    public function versionCompare($ver, $operator = '>=')
    {
        $version = $this->productMetadata->getVersion();
        return version_compare($version, $ver, $operator);
    }
}
