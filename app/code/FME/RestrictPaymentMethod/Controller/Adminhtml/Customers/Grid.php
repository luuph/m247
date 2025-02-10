<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Controller\Adminhtml\Customers;

use Magento\Cms\Model\Wysiwyg\Config;

class Grid extends \FME\RestrictPaymentMethod\Controller\Adminhtml\Customers\Customer
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    protected $registry;
    protected $paymentMethodFactory;
    private $wysiwygConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \FME\RestrictPaymentMethod\Model\PaymentMethodFactory $paymentMethodFactory,
        Config $wysiwygConfig
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry     = $registry;
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->wysiwygConfig = $wysiwygConfig;
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $item = $this->_initItem(true);
        if (!$item) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('paymentmethod/paymentmethod/new', ['_current' => true, 'id' => null]);
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'FME\RestrictPaymentMethod\Block\Adminhtml\RestrictPaymentMethod\Form\Tab\Customers',
                'category.customer.grid'
            )->toHtml()
        );
    }

    protected function _initItem($getRootInstead = false)
    {
        $id = (int) $this->getRequest()->getParam('id', false);
        $myModel =  $this->paymentMethodFactory->create();
        if ($id) {
            $myModel->load($id);
        }

        $this->registry->register('fme_paymentmethod', $myModel);
        return $myModel;
    }
}
