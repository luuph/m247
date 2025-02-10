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

namespace Magetrend\NewsletterMaker\Block\Adminhtml\Newsletter\Queue\Edit\Renderer;

/**
 * Iframe field renderer class
 */
class Preview extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    public $backendUrl;

    /**
     * Preview constructor.
     *
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->backendUrl = $backendUrl;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Retunrs element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $src = $this->getSrc();
        if (empty($src)) {
            return $html;
        }

        $html .= '<iframe src="'.$src.'" width="100%" height="720" frameborder="0" style="border: 0;"></iframe>';
        return $html;
    }

    /**
     * Returns iframe src attribute value
     *
     * @return string
     */
    public function getSrc()
    {
        $queue = $this->registry->registry('current_queue');
        if (!$queue || $queue->getTemplate()->getIsMtemail() != 1) {
            return '';
        }

        $templateId = $queue->getTemplate()->getId();

        return $this->backendUrl->getUrl('newslettermaker/mteditor/preview', ['template_id' => $templateId]);
    }
}
