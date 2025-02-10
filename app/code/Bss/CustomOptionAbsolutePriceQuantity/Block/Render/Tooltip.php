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
namespace Bss\CustomOptionAbsolutePriceQuantity\Block\Render;

use Bss\CustomOptionAbsolutePriceQuantity\Plugin\PriceType;

class Tooltip extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig
     */
    protected $moduleConfig;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Tooltip constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig $moduleConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig $moduleConfig,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
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
     * @return \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig
     */
    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    /**
     * @return bool
     */
    public function isAbsPriceTip()
    {
        if (!$this->isSelectType() && $this->getOption()->getPriceType() === PriceType::ABSOLUTE_PRICETYPE) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnableTooltip()
    {
        if (!$this->isSelectType()) {
            if ($this->getOption()->getPriceType() === 'abs') {
                return $this->moduleConfig->isEnableTooltip();
            }
        } else {
            if ($this->moduleConfig->isEnableTooltip()) {
                foreach ($this->getOption()->getValues() as $value) {
                    if ($value->getPriceType() === PriceType::ABSOLUTE_PRICETYPE) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isSelectType()
    {
        $type = $this->getOption()->getType();
        return $type === 'drop_down' || $type === 'radio' || $type === 'checkbox' || $type === 'multiple';
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
     * @return string
     */
    public function getPriceTypeData()
    {
        $result = [];
        if ($this->isStaticSelectType()) {
            foreach ($this->getOption()->getValues() as $value) {
                if ($value->getPriceType() === PriceType::ABSOLUTE_PRICETYPE) {
                    $result[] = $value->getOptionTypeId();
                }
            }
        }
        if (!empty($result)) {
            return $this->json->serialize($result);
        }
        return null;
    }
}
