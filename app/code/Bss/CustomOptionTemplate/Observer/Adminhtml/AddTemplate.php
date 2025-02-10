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
namespace Bss\CustomOptionTemplate\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

class AddTemplate implements ObserverInterface
{
    const BSS_TEMPLATE_DATA = 'check_bss_template_data';
    const BSS_OPTION_VISIBILITY = 'option_visibility';
    const BSS_OPTION_STORE_TITLE = 'option_store_title';

    /**
     * @var \Bss\CustomOptionTemplate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * AddTemplate constructor.
     * @param \Bss\CustomOptionTemplate\Helper\Data $helperData
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Helper\Data $helperData,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->helperData = $helperData;
        $this->json = $json;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $templateData = [
            80 => ['index' => static::BSS_TEMPLATE_DATA, 'field' => $this->getOptionTemplateData(80)],
            75 => ['index' => static::BSS_OPTION_VISIBILITY, 'field' => $this->getOptionVisibility(75)],
        ];

        $observer->getChild()->addData($templateData);
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionTemplateData($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Bss_CustomOptionTemplate/js/check-template-option',
                        'elementTmpl' => 'Bss_CustomOptionTemplate/check_template_option',
                        'label' => __('Option Template Data'),
                        'additionalClasses' => 'exclude_label_hidden',
                        'dataScope' => static::BSS_TEMPLATE_DATA,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionVisibility($sortOrder)
    {
        $listStoreView= $this->helperData->getListStoreView();
        $getListCustomerGroupArray = $this->helperData->getListCustomerGroupArray();
        $visibleData['visible_for_group_customer'] = $this->helperData->getCustomerGroupsId();
        $visibleData['visible_for_store_view'] = $this->helperData->getStoresId();

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Bss_CustomOptionTemplate/js/option-visibility',
                        'elementTmpl' => 'Bss_CustomOptionTemplate/option_visibility',
                        'label' => __('Option Template Data'),
                        'labelVisible' => false,
                        'dataScope' => static::BSS_OPTION_VISIBILITY,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'customer_group' => $getListCustomerGroupArray,
                        'value' => $this->json->serialize($visibleData),
                        'store_view' => $listStoreView
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionStoreTitle($sortOrder)
    {
        $getListCustomerGroupArray = $this->helperData->getListCustomerGroupArray();
        $listStoreView= $this->helperData->getListStoreView();
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Bss_CustomOptionTemplate/js/option-store-title',
                        'elementTmpl' => 'Bss_CustomOptionTemplate/option_store_title',
                        'label' => __('Option Template Data'),
                        'labelVisible' => false,
                        'dataScope' => static::BSS_OPTION_STORE_TITLE,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'customer_group' => $getListCustomerGroupArray,
                        'store_view' => $listStoreView
                    ],
                ],
            ],
        ];
    }
}
