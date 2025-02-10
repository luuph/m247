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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Observer\Adminhtml;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

class AddBackendField implements ObserverInterface
{
    const FIELD_QTY_OPTION = 'bss_coap_qty';
    const FIELD_BSS_DESCRIPTION_OPTION_TYPE = 'bss_description_option_type';
    const FIELD_BSS_DESCRIPTION_OPTION = 'bss_description_option';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManagerInterface;

    /**
     * AddBackendField constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ModuleConfig $moduleConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->urlBuilder = $urlBuilder;
        $this->_storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            $storeId = $this->_storeManagerInterface->getStore()->getId();

            $optionQtyField = [
                68 => ['index' => static::FIELD_BSS_DESCRIPTION_OPTION_TYPE, 'field' => $this->getTypeDescriptionCustomOptionField(68, $storeId)],
                69 => ['index' => static::FIELD_BSS_DESCRIPTION_OPTION, 'field' => $this->getDescriptionCustomOptionField(69, $storeId)],
                70 => ['index' => static::FIELD_QTY_OPTION, 'field' => $this->getQtyCustomOptionField(70)]
            ];

            $observer->getChild()->addData($optionQtyField);
        }
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getQtyCustomOptionField($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Qty'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_QTY_OPTION,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '0',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get field type short description.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getTypeDescriptionCustomOptionField($sortOrder, $storeId)
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Description Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_BSS_DESCRIPTION_OPTION_TYPE,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => [
                            ['value' => '0', 'label' => __('None')],
                            ['value' => '1', 'label' => __('Tooltip')],
                            ['value' => '2', 'label' => __('Small text')],
                        ],
                        'value' => '0',
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                            '__disableTmpl' => ['optionId' => false, 'isUseDefault' => false],
                        ],
                    ],
                ],
            ],
        ];

        if ($storeId) {
            $field['arguments']['data']['config']['imports']['isUseDefault'] = '${ $.provider }:${ $.parentScope }.is_use_default_bss_description_option_type';

            $useDefaultConfig = [
                'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
            ];
            $field['arguments']['data']['config']['service'] = $useDefaultConfig;
        }

        return $field;
    }

    /**
     * Get field short description.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getDescriptionCustomOptionField($sortOrder, $storeId)
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Short Description'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_BSS_DESCRIPTION_OPTION,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                            '__disableTmpl' => ['optionId' => false, 'isUseDefault' => false],
                        ],
                    ],
                ],
            ],
        ];

        if ($storeId) {
            $field['arguments']['data']['config']['imports']['isUseDefault'] = '${ $.provider }:${ $.parentScope }.is_use_default_bss_description_option';

            $useDefaultConfig = [
                'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
            ];
            $field['arguments']['data']['config']['service'] = $useDefaultConfig;
        }

        return $field;
    }
}
