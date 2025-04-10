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

/**
 * Webkul Walletsystem Class
 */
class SaveAccountDetails extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Walletsystem\Model\AccountDetails
     */
    protected $accountDetails;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\Walletsystem\Model\AccountDetails $accountDetails
     */
    public function __construct(
        Context $context,
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
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        if ($this->getRequest()->isPost()) {
            $accountDetails = $this->getRequest()->getParams();
            try {
                $this->accountDetails->setData($accountDetails)
                                    ->save();
                $this->messageManager->addSuccess(__('Account Information Saved Successfully'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/accountdetails',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
