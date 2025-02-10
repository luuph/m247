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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs;

use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Grid;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class transaction
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs
 */
class Transaction extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * Get after
     *
     * @return string
     */
    public function getAfter()
    {
        return 'wishlist';
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Transaction');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Transaction');
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * To html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_transaction');
        $form->addFieldset('base_fieldset', ['legend' => __('Transaction')]);

        $grid = $this->getLayout()
            ->createBlock(Grid::class, 'transaction.grid');

        return $form->toHtml() . $grid->toHtml();
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Get tab class
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }
}
