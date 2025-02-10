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
 * Save template information controller class
 */
class SaveInfo extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        $request = $this->getRequest();
        $templateCode = $request->getParam('template_code');
        $templateSenderEmail = $request->getParam('template_sender_email');
        $templateSenderName = $request->getParam('template_sender_name');
        $templateSubject = $request->getParam('template_subject');
        $template = $this->manager->initTemplate();

        if (!$template->getId() && $templateId) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $template->setTemplateSubject($templateSubject)
                    ->setTemplateSenderName($templateSenderName)
                    ->setTemplateSenderEmail($templateSenderEmail)
                    ->setTemplateCode($templateCode);
                $template->save();

                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
