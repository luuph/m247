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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\ViewModel\Adminhtml\Order\Create\Billing;

use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\SerializeData;
use Magento\Framework\Exception\LocalizedException;

class Address implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var SerializeData
     */
    protected $serializer;

    /**
     * @param Data $helperData
     * @param SerializeData $serializer
     */
    public function __construct(
        Data $helperData,
        SerializeData $serializer
    ) {
        $this->helperData = $helperData;
        $this->serializer = $serializer;
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->helperData->isEnableCustomerAttributeDependency();
    }

    /**
     * Encode function
     *
     * @param mixed|array $data
     * @return bool|string
     */
    public function encodeFunction($data)
    {
        return $this->serializer->encodeFunction($data);
    }

    /**
     * Decode function
     *
     * @param mixed|array $data
     * @return array|bool|float|int|string|null
     */
    public function decodeFunction($data)
    {
        return $this->serializer->decodeFunction($data);
    }

    /**
     * Same 100% core
     *
     * @param string $style
     * @param string $selector
     * @return string
     * @throws LocalizedException
     */
    public function renderStyleAsTag($style, $selector)
    {
        return $this->helperData->renderStyleAsTag($style, $selector);
    }

    /**
     * Render tag
     *
     * @param string $tagName
     * @param array $attributes
     * @param ?string $content
     * @return string
     */
    public function renderTag($tagName, $attributes, $content = null, $textContent = true)
    {
        return $this->helperData->renderTag($tagName, $attributes, $content, $textContent);
    }

    /**
     * Render event listener
     *
     * @param string $eventName
     * @param string $attributeJavascript
     * @param string $elementSelector
     * @param bool $textContent
     * @return string
     * @throws LocalizedException
     */
    public function renderEventListenerAsTag(
        $eventName,
        $attributeJavascript,
        $elementSelector,
        $textContent = true
    ) {
        return $this->helperData->renderEventListenerAsTag($eventName, $attributeJavascript, $elementSelector, $textContent);
    }
}