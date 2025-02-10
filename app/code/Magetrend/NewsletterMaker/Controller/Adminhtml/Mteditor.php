<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace  Magetrend\NewsletterMaker\Controller\Adminhtml;

use Magento\Framework\Json\Helper\Data;

/**
 * Abstract mteditor controller class
 */
abstract class Mteditor extends \Magento\Backend\App\Action
{
    public $resultJsonFactory = null;

    public $coreRegistry = null;

    public $sessionManager = null;

    public $jsonHelper;

    public $manager;

    public $templateFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magetrend\NewsletterMaker\Model\MteditorManager $manager,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreRegistry = $coreRegistry;
        $this->sessionManager = $session;
        $this->jsonHelper = $jsonHelper;
        $this->manager = $manager;
        $this->templateFactory = $templateFactory;
        parent::__construct($context);
    }

    protected function _error($message)
    {
        return $this->resultJsonFactory->create()->setData([
            'error' => $message
        ]);
    }

    protected function _jsonResponse($data)
    {
        return $this->resultJsonFactory->create()->setData($data);
    }

    /**
     * Validate extension configuration
     * @param int $storeId
     *
     * @return boolean
     */
    protected function _validateConfig($storeId)
    {
        return true;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::template');
    }
}
