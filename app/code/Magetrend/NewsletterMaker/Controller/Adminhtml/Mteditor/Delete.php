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
 * Delete newsletter template controller class
 */
class Delete extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{
    
    public function execute()
    {
        $template = $this->manager->initTemplate();

        if (!$template->getId()) {
            return $this->_error(__('This Email template no longer exists.'));
        } else {
            try {
                $template->delete();
                return $this->_jsonResponse([
                    'success' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->_error($e->getMessage());
            }
        }
    }
}
