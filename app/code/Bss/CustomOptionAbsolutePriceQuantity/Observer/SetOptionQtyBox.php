<?php
declare(strict_types=1);
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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOptionAbsolutePriceQuantity\Observer;

use Magento\Framework\Event\Observer;

class SetOptionQtyBox implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $childObject */
        $childObject = $observer->getChild();
        $childObject->setData('absolute_price_qty_qtybox', \Bss\CustomOptionAbsolutePriceQuantity\Block\Render\QtyBox::class);
    }
}
