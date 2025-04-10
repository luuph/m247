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

/**
 * Webkul Walletsystem Class
 */
class Refund extends WalletController
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $log
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $log,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->log = $log;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $params = $this->getRequest()->getPost();
            $refundInWallet = $params['refundInWallet'];
            $this->sessionManager->setWalletUsedForReturn($refundInWallet);
            $result->setData(true);
        } catch (\Exception $e) {
            $result->setData(false);
            $this->log->addError($e->getMessage());
        }
        return $result;
    }
}
