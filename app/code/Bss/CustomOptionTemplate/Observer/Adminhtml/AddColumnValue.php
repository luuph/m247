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
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Checkbox;

class AddColumnValue implements ObserverInterface
{
    const BSS_OPTION_IS_DEFAULT = 'is_default';

    /**
     * @var \Bss\CustomOptionTemplate\Helper\Data
     */
    protected $helperData;

    /**
     * AddColumnValue constructor.
     * @param \Bss\CustomOptionTemplate\Helper\Data $helperData
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $templateData = [
            289 => ['index' => static::BSS_OPTION_IS_DEFAULT, 'field' => $this->getOptionIsDefault(289)],

        ];
        $observer->getChild()->addData($templateData);
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionIsDefault($sortOrder)
    {
        $listStoreView= $this->helperData->getListStoreView();
        $getListCustomerGroupArray = $this->helperData->getListCustomerGroupArray();
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Is Default'),
                        'componentType' => Field::NAME,
                        'component'     => 'Bss_CustomOptionTemplate/js/is-default',
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => static::BSS_OPTION_IS_DEFAULT,
                        'dataType'      => Number::NAME,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => '1',
                            'false' => '0',
                        ],
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                        'customer_group' => $getListCustomerGroupArray,
                        'store_view' => $listStoreView
                    ],
                ],
            ],
        ];
    }
}
