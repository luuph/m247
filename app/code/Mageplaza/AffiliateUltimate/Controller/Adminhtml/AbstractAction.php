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

namespace Mageplaza\AffiliateUltimate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AffiliateUltimate\Helper\Data as HelperData;

/**
 * Class AbstractAction
 * @package Mageplaza\AffiliateUltimate\Controller\Adminhtml
 */
abstract class AbstractAction extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HelperData $helperData
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->helperData         = $helperData;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        if ($this->helperData->canUseStoreSwitcherLayoutByMpReports()) {
            $resultPage->addHandle('store_switcher');
        }

        $resultPage->getConfig()->getTitle()->prepend($this->getPageTitle());

        return $resultPage;
    }

    /**
     * @return Phrase
     */
    public function getPageTitle()
    {
        return __('Accounts Reports');
    }
}
