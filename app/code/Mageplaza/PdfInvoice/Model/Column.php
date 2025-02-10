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

namespace Mageplaza\PdfInvoice\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Template
 * @package Mageplaza\PdfInvoice\Model
 */
class Column extends AbstractModel
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Mageplaza\PdfInvoice\Model\ResourceModel\Column::class);
    }
}
