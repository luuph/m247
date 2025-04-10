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

namespace Mageplaza\AffiliatePro\Model\ResourceModel;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Model\ResourceModel
 */
class Banner extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_affiliate_banner', 'banner_id');
    }

    /**
     * before save callback
     *
     * @param AbstractModel|\Mageplaza\Affiliate\Model\Banner $object
     *
     * @return $this
     * @throws Exception
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        if ($object->isObjectNew()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        return parent::_beforeSave($object);
    }
}
