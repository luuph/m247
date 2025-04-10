<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateInvoiceInterface;
use Mageplaza\AffiliateUltimate\Api\Data\AffiliateInvoiceItemInterface;

/**
 * Interface InvoiceRepositoryInterface
 * @api
 */
interface InvoiceRepositoryInterface
{
    /**
     * @param int $invoiceId The Invoice ID.
     *
     * @return AffiliateInvoiceInterface Affiliate.
     * @throws NoSuchEntityException
     */
    public function get($invoiceId);

    /**
     * @param int $itemId The item ID.
     *
     * @return AffiliateInvoiceItemInterface Affiliate item.
     * @throws NoSuchEntityException
     */
    public function getItemById($itemId);
}
