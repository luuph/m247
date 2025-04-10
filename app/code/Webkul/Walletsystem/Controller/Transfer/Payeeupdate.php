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
class Payeeupdate extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param WalletUpdateData $walletUpdate
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletUpdate = $walletUpdate;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $result = [
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];
            return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($result)
            );
        }
        $params = $this->getRequest()->getParams();
        $result = $this->validateParams($params);
        if (!$this->getRequest()->isAjax()) {
            return $this->resultRedirectFactory->create()->setPath(
                'walletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return array
     */
    protected function validateParams($params)
    {
        $result = [
            'error' => 0
        ];
        $error = 0;
        if (isset($params) && is_array($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'id':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                    case 'nickname':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                }
            }
            if ($error==1) {
                $result['error'] = 1;
                $result['error_msg'] = __("Please try again later");
                $this->messageManager->addError(__("Please try again later"));
            } else {
                $result = $this->updatePayeeNickName($params);
            }
        }
        return $result;
    }

    /**
     * Update payee nick name
     *
     * @param array $params
     * @return array
     */
    public function updatePayeeNickName($params)
    {
        $payeeModel = $this->walletPayee->create()->load($params['id']);
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = $payeeModel::PAYEE_STATUS_ENABLE;
        } else {
            $status = $payeeModel::PAYEE_STATUS_DISABLE;
        }
        $payeeModel->setNickName($params['nickname'])
            ->save();
        $this->messageManager->addSuccess(__("Payee is updated"));
        $result = [
            'error' => 0,
            'success_msg' => __('Payee is updated')
        ];
        return $result;
    }
}
