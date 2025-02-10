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
 * Send test email controller class
 */
class Send extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('id');
        $source = $this->getRequest()->getParam('source');
        $email = $this->getRequest()->getParam('email');
        $template = $this->manager->initTemplate('id');

        $source = json_decode($source);

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $this->manager->sendTestEmail($email, $template, $source);
                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
