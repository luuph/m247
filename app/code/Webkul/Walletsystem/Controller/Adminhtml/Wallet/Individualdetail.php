<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

/**
 * Webkul Walletsystem Class
 */
class Individualdetail extends WalletController
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerModel
    ) {
        parent::__construct($context);
        $this->customerModel = $customerModel;
    }

   /**
    * Execute
    *
    * @return \Magento\Backend\Model\View\Result\Page
    */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customer = $this->customerModel
            ->create()
            ->load($params['customer_id']);
        if (!empty($customer) && $customer->getEntityId()) {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->setActiveMenu('Webkul_Walletsystem::walletsystem');
            $resultPage->getConfig()->getTitle()
                ->prepend(ucwords(
                    __(
                        "%1's details",
                        $customer->getName()
                    )
                ));
            $resultPage->addBreadcrumb(
                __("%1's details", $customer->getName()),
                __("%1's details", $customer->getName())
            );
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(
                __('Customer does not exists.')
            );
            return $resultRedirect->setPath('walletsystem/wallet/index');
        }
    }
}
