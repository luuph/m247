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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Controller\Index;

use Magento\Framework\App\Action;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class LoadItem extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    private $helperBss;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
     */
    public function __construct(
        Action\Context $context,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->helperBss = $helperBss;
        $this->checkoutSession = $checkoutSession;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Get qty item in cart when edit product
     *
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        try {
            $itemId = $this->getRequest()->getParam('item_id');
            $productId = $this->getRequest()->getParam('product');
            $quote = $this->checkoutSession->create()->getQuote();
            $itemCurrent = $this->getCurrentItem($quote, $itemId);
            $this->validateController($itemId, $productId, $itemCurrent);
            $optionsCurrent = $this->_getOptionProduct($itemCurrent);
            $customOptionsCurrent = $this->_getCustomOption($optionsCurrent);
            $productApply = [];
            $childApply = [];
            $respond = [];
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                if ($item->getProduct()->getId() == $productId) {
                    $options = $this->_getOptionProduct($item);
                    if ($this->_checkItem($options, $customOptionsCurrent)) {
                        $productApply[$item->getId()] = [
                            'qty' => $item->getQty(),
                            'data' => $this->_getOptionData($options)
                        ];
                    }
                } else {
                    $parentItem = $item->getParentItem();
                    if (isset($parentItem) && $parentItem->getProduct()->getId() == $productId) {
                        $childApply[$parentItem->getId()] = $item->getProduct()->getId();
                    }
                }
            }
            foreach ($productApply as $id => $data) {
                $productId = $childApply[$id];
                $respond['product'][$productId] = $data;
                $respond['item'][$productId] = $id;
            }
            $respond['default'] = $childApply[$itemId];
            return $result->setData($respond);
        } catch (\Exception $e) {
            return $result->setData([]);
        }
    }

    /**
     * @param int $itemId
     * @param int $productId
     * @param \Magento\Quote\Model\Quote\Item|false $itemCurrent
     * @throws \Exception
     */
    private function validateController($itemId, $productId, $itemCurrent)
    {
        if (!($this->helperBss->isModuleEnabled() && $itemId && $productId && $itemCurrent)) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Cannot load item"));
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $itemId
     * @return \Magento\Quote\Model\Quote\Item|false
     * @throws \Exception
     */
    protected function getCurrentItem($quote, $itemId)
    {
        $result = $quote->getItemById($itemId);
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Cannot load item"));
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return bool|array
     */
    protected function _getOptionProduct($item)
    {
        if ($item) {
            $product = $item->getProduct();
            return $product->getTypeInstance()->getOrderOptions($product);
        }
        return false;
    }

    /**
     * @param array $options
     * @param array $customOptionsCurrent
     * @return bool
     */
    protected function _checkItem($options, $customOptionsCurrent)
    {
        if (!isset($options['options'])) {
            return empty($customOptionsCurrent);
        }
        if (count($options['options']) !== count($customOptionsCurrent)) {
            return false;
        }
        foreach ($options['options'] as $key => $option) {
            $result = array_diff($option, $customOptionsCurrent[$key]);
            if ($result) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $optionsCurrent
     * @return array
     */
    protected function _getCustomOption($optionsCurrent)
    {
        $customOptionsCurrent = [];
        if (isset($optionsCurrent['options'])) {
            $customOptionsCurrent = $optionsCurrent['options'];
        }
        return $customOptionsCurrent;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function _getOptionData($options)
    {
        $option = [];
        if (isset($options['attributes_info']) && !empty($options['attributes_info'])) {
            foreach ($options['attributes_info'] as $attr) {
                $optionId = $attr['option_id'];
                $option['data-option-'.$optionId] = $attr['option_value'];
            }
        }
        return $option;
    }
}
