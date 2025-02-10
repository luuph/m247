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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Block\Adminhtml\Render;

use Bss\CustomOptionAbsolutePriceQuantity\Plugin\PriceType;

class OrderOptionTip extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * OrderOptionTip constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->setTemplate('Bss_CustomOptionAbsolutePriceQuantity::render/tooltip.phtml');
    }

    /**
     * @return bool
     */
    public function isStaticSelectType()
    {
        $type = $this->getOption()->getType();
        return $type === 'radio' || $type === 'checkbox';
    }

    /**
     * @return string|null
     */
    public function getPriceTypeData()
    {
        $result = [];
        foreach ($this->getOption()->getValues() as $value) {
            if ($value->getPriceType() === PriceType::ABSOLUTE_PRICETYPE) {
                $result[] = $value->getOptionTypeId();
            }
        }
        if (!empty($result)) {
            return $this->json->serialize($result);
        }
        return null;
    }
}
