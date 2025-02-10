<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Block\Adminhtml\Template\Edit;

use Mageplaza\PdfInvoice\Model\ResourceModel\Column\Collection;

/**
 * Class Tabs
 * @package Mageplaza\PdfInvoice\Block\Adminhtml\Template\Edit
 */
class Column extends \Magento\Backend\Block\Template
{
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $columnCollectionFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Collection $columnCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->columnCollectionFactory = $columnCollectionFactory;
    }

    /**
     * Retrieve grouped products
     *
     * @return array
     */
    public function getColumns()
    {
        /** @var $product \Mageplaza\PdfInvoice\Model\Column */
        $templateID = $this->getRequest()->getParam('id');
        $columnCollection = $this->columnCollectionFactory->addFieldToFilter('template_id', $templateID)->setOrder('position', 'ASC');;
        $columns = [];
        if ($columnCollection->getSize() === 0) {
            $columns = [
                [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Items',
                    'position' => 1
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Code',
                    'position' => 2
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Qty',
                    'position' => 3
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Price',
                    'position' => 4
                ], [
                    'id'       => null,
                    'status'   => 1,
                    'name'     => 'Subtotal',
                    'position' => 5
                ], [
                    'id'       => null,
                    'status'   => 0,
                    'name'     => 'Tax',
                    'position' => 6
                ], [
                    'id'       => null,
                    'status'   => 0,
                    'name'     => 'Discount',
                    'position' => 7
                ]

            ] ;
        } else {
            foreach ($columnCollection as $column) {
                $columns[] = [
                    'id'       => $column->getColumnId(),
                    'status'   => $column->getStatus(),
                    'name'     => $column->getName(),
                    'position' => $column->getPosition(),
                ];
            }
        }
        return $columns;
    }
}
