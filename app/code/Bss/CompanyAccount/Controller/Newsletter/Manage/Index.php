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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Controller\Newsletter\Manage;

use Bss\CompanyAccount\Helper\Data;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Newsletter\Controller\Manage\Index
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helper
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Data $helper,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct(
            $context,
            $customerSession
        );
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if ($this->helper->isEnable() && $this->helper->getCustomerSession()->getSubUser()) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noRoute');
        }
        return parent::execute();
    }
}
