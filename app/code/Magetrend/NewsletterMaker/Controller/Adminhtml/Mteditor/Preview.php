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
 * Preview template controller class
 */
class Preview extends \Magetrend\NewsletterMaker\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $template = $this->manager->initTemplate('template_id');
        $layout = $this->_view->getLayout();
        $block = $layout->createBlock(\Magento\Backend\Block\Template::class);
        $block->setTemplate('Magetrend_NewsletterMaker::mteditor/preview.phtml')
            ->setNewsletterTemplate($template);

        $this->getResponse()->setBody($block->toHtml());
    }
}
