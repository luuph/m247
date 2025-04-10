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

use Mageplaza\AffiliateUltimate\Api\CreditmemoRepositoryInterface;
use Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoFactory;
use Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoItemFactory;

/**
 * Class CreditmemoRepository
 * @package Mageplaza\AffiliateUltimate\Model
 */
class CreditmemoRepository implements CreditmemoRepositoryInterface
{
    /**
     * @var \Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoFactory
     */
    protected $affiliateCreditmemoFactory;

    /**
     * @var \Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoItemFactory
     */
    protected $affiliateCreditmemoItemFactory;

    /**
     * CreditmemoRepository constructor.
     *
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoFactory $affiliateCreditmemoFactory
     * @param \Mageplaza\AffiliateUltimate\Model\AffiliateCreditmemoItemFactory $affiliateCreditmemoItemFactory
     */
    public function __construct(
        AffiliateCreditmemoFactory $affiliateCreditmemoFactory,
        AffiliateCreditmemoItemFactory $affiliateCreditmemoItemFactory
    ) {
        $this->affiliateCreditmemoFactory = $affiliateCreditmemoFactory;
        $this->affiliateCreditmemoItemFactory = $affiliateCreditmemoItemFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($invoiceId)
    {
        return $this->affiliateCreditmemoFactory->create()->load($invoiceId);
    }

    /**
     * {@inheritDoc}
     */
    public function getItemById($itemId)
    {
        return $this->affiliateCreditmemoItemFactory->create()->load($itemId);
    }
}
