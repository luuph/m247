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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionTemplate\Block\Render;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class IsDefault extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * IsDefault constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->json = $json;
        $this->registry       = $registry;
    }

    /**
     * @return string
     */
    public function getIsDefaultOptionJsonData()
    {
        return $this->json->serialize($this->getIsDefaultArray($this->registry->registry('product')));
    }

    /**
     * Get is default config array
     *
     * @param mixed $product
     * @return array
     */
    public function getIsDefaultArray($product)
    {
        $result = [];
        if (!empty($product->getOptions())) {
            foreach ($product->getOptions() as $option) {
                if (!empty($option->getValues())) {
                    $result[$option->getId()]['type'] = $option->getType();
                    foreach ($option->getValues() as $value) {
                        if ($value->getData('is_default') && $value->getData('is_default') !== 0) {
                            $result[$option->getId()]['selected'][] = $value->getOptionTypeId();
                        }
                    }
                }
            }
        }
        return $result;
    }
}
