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

namespace Mageplaza\AffiliateUltimate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Form
 * @package Mageplaza\AffiliateUltimate\Observer
 */
class Form implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form     = $observer->getEvent()->getForm();
        $banner   = $observer->getEvent()->getBanner();

        $fieldset = $form->addFieldset('statistic', [
            'legend' => __('Statistic')
        ]);

        $fieldset->addField('ctr', 'note', [
            'name' => 'click_through_rate',
            'note' => __('CTR'),
            'text' => $banner->getCtr() . '%'
        ]);

        $fieldset->addField('click', 'note', [
            'name' => 'click',
            'note' => __('Click(s)'),
            'text' => $banner->getClick()
        ]);

        $fieldset->addField('impression', 'note', [
            'name' => 'impression',
            'note' => __('Impression(s)'),
            'text' => $banner->getImpression()
        ]);

        return $this;
    }
}
