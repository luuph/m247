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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\PriceAlert;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Display customer product price alert
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|(\Magento\Framework\View\Result\Page&\Magento\Framework\Controller\ResultInterface)
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->helper->isEnablePriceAlertAndCustomer() || !$this->customerSession->isLoggedIn()) {
            throw new NotFoundException(__('Please login before seeing the page.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
