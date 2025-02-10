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
 * Upload template controller class
 */
class UploadTemplate extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{

    public function execute()
    {
        try {
            $file = $this->manager->uploadTemplate();
            $result = [
                'success' => 1,
                'file' => $file
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->_error($e->getMessage());
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->_jsonResponse($result);
    }
}
