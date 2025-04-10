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

namespace Webkul\Walletsystem\Controller\Adminhtml\Transfer;

use Webkul\Walletsystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Walletsystem Class
 */
class DeleteAccountDetails extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\Walletsystem\Model\AccountDetails
     */
    public $accountDetails;
    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\Walletsystem\Model\AccountDetails $accountDetails
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails
    ) {
        $this->accountDetails = $accountDetails;
        parent::__construct($context);
    }

    /**
     * Controller execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        
        if ($this->getRequest()->getParams('id')) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->accountDetails->load($id)
                                    ->setStatus('0')
                                    ->save();
                $this->messageManager->addSuccess('Deleted Successfully');
            } else {
                $this->messageManager->addWarning('Please check the data entered');
            }
        } else {
            $this->messageManager->addWarning('Please check the data entered');
        }

         return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                                    ->setPath('*/*/accountcustomerlist');
    }
}
