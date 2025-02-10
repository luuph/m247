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
namespace Bss\RewardPoint\Controller\Adminhtml\Customer\Balance;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

/**
 * Class customergrid
 *
 * Bss\RewardPoint\Controller\Adminhtml\Customer\Balance
 */
class Customergrid extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Customergrid constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->resultPageFactory = $context->getResultFactory();
        $this->registry = $registry;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        $customer = $this->customerFactory->create()->load($id);
        $this->registry->register('current_customer', $customer);
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());

        $this->getResponse()->setBody(
            $resultPage->getLayout()
                ->createBlock(
                    \Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Grid::class,
                    'balance.grid'
                )
                ->toHtml()
        );
        return $resultPage;
    }
}
