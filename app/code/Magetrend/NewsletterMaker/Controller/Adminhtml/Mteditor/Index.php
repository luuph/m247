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
namespace Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor;

/**
 * MTEditor controller class
 */
class Index extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $template = $this->manager->initTemplate('id');
        $this->manager->setEditMode();
        if ($template->getId()) {
            if (!$this->_validateConfig($template->getStoreId())) {
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/email_template/index');
            }
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('MT Editor / Magento Admin'));

        $this->_view->renderLayout();
    }
}
