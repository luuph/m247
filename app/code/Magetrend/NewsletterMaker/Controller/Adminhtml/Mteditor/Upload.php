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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Upload image controller class
 */
class Upload extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        $template = $this->manager->initTemplate('id');
        if (!$template->getId()) {
            return $this->_error('Template is no longer available');
        }

        try {
            $fileUrl = $this->manager->uploadImage($template->getId());
            $result = [
                'success' => 1,
                'fileUrl' => $fileUrl,
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->_jsonResponse($result);
    }
}
