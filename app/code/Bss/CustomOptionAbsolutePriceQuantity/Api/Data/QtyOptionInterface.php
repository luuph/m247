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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Api\Data;

interface QtyOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const OPTION_ID = 'option_id';

    const OPTION_QTY = 'option_qty';

    /**#@-*/

    /**
     * Get option SKU
     *
     * @return string
     */
    public function getOptionId();

    /**
     * Set option SKU
     *
     * @param string $value
     * @return void
     */
    public function setOptionId($value);

    /**
     * Get item id
     *
     * @return int|null
     */
    public function getOptionQty();

    /**
     * Set item id
     *
     * @param int|null $value
     * @return void
     */
    public function setOptionQty($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Bss\CustomOptionAbsolutePriceQuantity\Api\Data\QtyOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Api\Data\QtyOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Bss\CustomOptionAbsolutePriceQuantity\Api\Data\QtyOptionExtensionInterface $extensionAttributes
    );
}
