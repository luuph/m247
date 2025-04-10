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

use \Magento\Backend\Model\View\Result\ForwardFactory;
use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;

/**
 * Webkul Walletsystem Class
 */
class Newcreditrule extends CreditrulesController
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    private $resultForwardFactory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param ForwardFactory                      $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
