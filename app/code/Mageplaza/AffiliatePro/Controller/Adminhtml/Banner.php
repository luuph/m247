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

namespace Mageplaza\AffiliatePro\Controller\Adminhtml;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Controller\Adminhtml
 */
abstract class Banner extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_AffiliatePro::banner';

    /**
     * @return \Mageplaza\AffiliatePro\Model\Banner
     */
    protected function _initBanner()
    {
        $bannerId = (int)$this->getRequest()->getParam('id');
        /** @var \Mageplaza\AffiliatePro\Model\Banner $banner */
        $banner = $this->_objectManager->create('Mageplaza\AffiliatePro\Model\Banner');
        if ($bannerId) {
            $banner->load($bannerId);
        }
        if (!$this->_coreRegistry->registry('current_banner')) {
            $this->_coreRegistry->register('current_banner', $banner);
        }

        return $banner;
    }
}
