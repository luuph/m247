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

namespace Mageplaza\AffiliateUltimate\Model;

use Mageplaza\AffiliateUltimate\Api\InvoiceRepositoryInterface;
use Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceFactory;
use Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceItemFactory;

/**
 * Class InvoiceRepository
 * @package Mageplaza\AffiliateUltimate\Model
 */
class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * @var \Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceFactory
     */
    protected $affiliateInvoiceFactory;

    /**
     * @var \Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceItemFactory
     */
    protected $affiliateInvoiceItemFactory;

    /**
     * InvoiceRepository constructor.
     *
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceFactory $affiliateInvoiceFactory
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateInvoiceItemFactory $affiliateInvoiceItemFactory
     */
    public function __construct(
        AffiliateInvoiceFactory $affiliateInvoiceFactory,
        AffiliateInvoiceItemFactory $affiliateInvoiceItemFactory
    ) {
        $this->affiliateInvoiceFactory = $affiliateInvoiceFactory;
        $this->affiliateInvoiceItemFactory = $affiliateInvoiceItemFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($invoiceId)
    {
        return $this->affiliateInvoiceFactory->create()->load($invoiceId);
    }

    /**
     * {@inheritDoc}
     */
    public function getItemById($itemId)
    {
        return $this->affiliateInvoiceItemFactory->create()->load($itemId);
    }
}
