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

namespace Webkul\Walletsystem\Controller\Adminhtml\Creditrules;

use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletcreditrulesFactory;

/**
 * Webkul Walletsystem Class
 */
class Edit extends CreditrulesController
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Webkul\Walletsystem\Model\WalletcreditrulesFactory
     */
    private $walletcreditrulesFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param WalletcreditrulesFactory $walletcreditrulesFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        WalletcreditrulesFactory $walletcreditrulesFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->walletcreditrulesFactory = $walletcreditrulesFactory;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Walletsystem::walletcreditrules')
            ->addBreadcrumb(__('Wallet System Credit Rule'), __('Wallet System Credit Rule'));
        return $resultPage;
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->walletcreditrulesFactory
            ->create();
        if ($id) {
            $model->load($id);
            $flag = 1;
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('walletsystem/creditrules/creditrules');
            }
        }
        $data = $this->_session
                ->getFormData(true);

        if (isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        $this->coreRegistry->register('wallet_creditrule', $model);
        $resultPage = $this->_initAction();
        if ($flag==1 && $id) {
            $resultPage->addBreadcrumb(__('Edit Wallet Credit Rule'), __('Edit Wallet Credit Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Credit Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Wallet Credit Rule'), __('Add Wallet Credit Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Credit Rule'));
        }
        return $resultPage;
    }
}
