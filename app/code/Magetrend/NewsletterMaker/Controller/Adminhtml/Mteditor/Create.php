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
 * Create newsletter template controller class
 */
class Create extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{
    public $emailConfig = null;

    public function execute()
    {
        $request = $this->getRequest();
        $templateId = $request->getParam('template_id');
        $subject = $request->getParam('template_subject');
        $name = $request->getParam('template_name');

        try {
            if (is_numeric($templateId)) {
                $template = $this->manager->duplicateTemplate($templateId, $name, $subject);
            } else {
                $template = $this->manager->createFromImportedFile($name, $subject);
            }

            return $this->_jsonResponse([
                'success' => 1,
                'redirectTo' => $this->getUrl("*/*/index/", ['id' => $template->getId()])
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->_error($e->getMessage());
        } catch (\Exception $e) {
            return $this->_error($e->getMessage());
        }
    }
}
