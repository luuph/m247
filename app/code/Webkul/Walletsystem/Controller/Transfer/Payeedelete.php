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

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\Wallettransaction;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul Walletsystem Class
 */
class Payeedelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $walletHelper;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * @var  CustomerFactory
     */
    protected $customerModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var WalletPayeeFactory
     */
    protected $walletPayee;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param WalletUpdateData $walletUpdate
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletUpdate = $walletUpdate;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return string
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     */
    protected function validateParams($params)
    {
        if (isset($params)
            && is_array($params)
            && array_key_exists('id', $params)
            && $params['id']!='') {
            $this->deletePayee($params);
        } else {
            $this->messageManager->addError(
                __("There is some error during executing this process, please try again later.")
            );
        }
    }

    /**
     * Delete payee
     *
     * @param array $params
     */
    public function deletePayee($params)
    {
        $customerId = $this->walletHelper->getCustomerId();
        $payeeModel = $this->walletPayee->create()->getCollection()
        ->addFieldToFilter('customer_id', $customerId)
        ->addFieldToFilter('entity_id', $params['id']);
        if ($payeeModel->getSize()) {
            $payeeModel = $payeeModel->getFirstItem();
            $payeeModel->delete();
            $this->messageManager->addSuccess(__("Payee is successfully deleted"));
        } else {
            $this->messageManager->addError(__("Invalid data"));
        }
    }
}
