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
namespace Bss\CustomOptionTemplate\Model\Option;

use Magento\Framework\Model\AbstractModel;

class Value extends AbstractModel
{
    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\CustomOptionTemplate\Model\ResourceModel\Option\Value::class);
    }

    /**
     * @param int $optionId
     * @param int $templateOptionTypeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseOptionTypeId($optionId, $templateOptionTypeId)
    {
        return $this->_getResource()->getBaseOptionTypeId($optionId, $templateOptionTypeId);
    }
}
