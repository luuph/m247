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

namespace Mageplaza\Affiliate\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Affiliate\Controller\Adminhtml\Account;
use Mageplaza\Affiliate\Model\AccountFactory;

/**
 * Class Index
 * @package Mageplaza\Affiliate\Controller\Adminhtml\Transaction
 */
class Logs extends Account
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Logs constructor.
     *
     * @param Context $context
     * @param AccountFactory $accountFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
       Context $context,
       AccountFactory $accountFactory,
       Registry $coreRegistry,
       PageFactory $resultPageFactory,
       LayoutFactory $resultLayoutFactory

   ) {
       $this->resultLayoutFactory = $resultLayoutFactory;
       parent::__construct($context, $accountFactory, $coreRegistry, $resultPageFactory);
   }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        /** @var \Mageplaza\Affiliate\Model\Account $account */
        $account = $this->_initAccount();
        $this->_coreRegistry->register('current_account', $account);
        return $this->resultLayoutFactory->create();
    }
}
