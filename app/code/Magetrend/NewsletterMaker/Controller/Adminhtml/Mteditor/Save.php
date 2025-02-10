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
 * Save template controller class
 */
class Save extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        $head = $this->getRequest()->getParam('head');
        $body = $this->getRequest()->getParam('body');
        $template = $this->manager->initTemplate();

        $head = json_decode($head);
        $body = json_decode($body);

        if (!$template->getId()) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $this->manager->saveTemplate($template, $head, $body);
                $template = $this->manager->initTemplate();
                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
