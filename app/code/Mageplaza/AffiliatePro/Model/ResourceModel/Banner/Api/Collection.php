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
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Model\ResourceModel\Banner\Api;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\AffiliatePro\Api\Data\BannerSearchResultInterface;

/**
 * Class Collection
 * @api
 * @package Mageplaza\AffiliatePro\Model\ResourceModel\Banner\Api
 */
class Collection extends AbstractCollection implements BannerSearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'banner_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'affiliate_banner_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'affiliate_banner_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Mageplaza\AffiliatePro\Model\Api\Banner',
            'Mageplaza\AffiliatePro\Model\ResourceModel\Banner'
        );
    }
}
