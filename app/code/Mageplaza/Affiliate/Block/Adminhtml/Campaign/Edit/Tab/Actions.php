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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Mageplaza\Affiliate\Helper\Data;
use Mageplaza\Affiliate\Model\Account\Group;
use Mageplaza\Affiliate\Model\Account\Status;
use Mageplaza\Affiliate\Model\AccountFactory;

/**
 * Class TransactionLogs
 *
 * @package Mageplaza\Affiliate\Block\Adminhtml\Account\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var Yesno
     */
    protected $_boolean;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var Group
     */
    protected $_group;

    /**
     * @var AccountFactory
     */
    protected $_accountFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Account constructor.
     *
     * @param Yesno $boolean
     * @param Status $status
     * @param Group $group
     * @param Context $context
     * @param Registry $registry
     * @param CustomerFactory $customerFactory
     * @param AccountFactory $accountFactory
     * @param FormFactory $formFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Yesno $boolean,
        Status $status,
        Group $group,
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        AccountFactory $accountFactory,
        FormFactory $formFactory,
        Data $helperData,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_accountFactory  = $accountFactory;
        $this->_customerFactory = $customerFactory;
        $this->_boolean         = $boolean;
        $this->_status          = $status;
        $this->_group           = $group;
        $this->helperData       = $helperData;
        $this->priceCurrency    = $priceCurrency;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Affiliate\Model\Account $account */
        $account = $this->_coreRegistry->registry('current_campaign_rule');
        $form    = $this->_formFactory->create();
        $form->setHtmlIdPrefix('account_');
        $form->setFieldNameSuffix('account');
        $form->addValues($account->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();
        $childHtml = $this->getChildHtml();

        return $formHtml . $childHtml;
    }
}
