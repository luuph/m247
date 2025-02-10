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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * To get all attribute of table
 */
class Collection extends AbstractCollection
{
    /**
     * Collection initialize with 2 param is model and resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Bss\DynamicCategory\Model\Rule::class, \Bss\DynamicCategory\Model\ResourceModel\Rule::class);
    }
}
