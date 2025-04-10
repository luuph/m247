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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Model\ResourceModel\Transaction;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\AffiliateUltimate\Api\Data\TransactionSearchResultInterface;

/**
 * Class Collection
 * @api
 * @package Mageplaza\AffiliateUltimate\Model\ResourceModel\Transaction
 */
class Collection extends AbstractCollection implements TransactionSearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'affiliate_transaction_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'affiliate_transaction_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Mageplaza\AffiliateUltimate\Model\Transaction',
            'Mageplaza\Affiliate\Model\ResourceModel\Transaction'
        );
    }
}
