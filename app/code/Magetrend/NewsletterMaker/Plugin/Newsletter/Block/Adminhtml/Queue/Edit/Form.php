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

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Block\Adminhtml\Queue\Edit;

class Form
{
    /**
     * @var \Magetrend\NewsletterMaker\Helper\Data
     */
    public $mtHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $templateHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\NewsletterMaker\Helper\Data $helper,
        \Magetrend\NewsletterMaker\Helper\Template $templateHelper
    ) {
        $this->mtHelper = $helper;
        $this->registry = $registry;
        $this->templateHelper = $templateHelper;
    }
    public function afterSetForm(\Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form $subject, $mainObject)
    {
        $queue = $this->registry->registry('current_queue');
        if (!$queue || $queue->getTemplate()->getIsMtemail() != 1) {
            return $mainObject;
        }

        $fieldset = $subject->getForm()->getElement('base_fieldset');

        $elements = $fieldset->getElements();
        $fieldset->removeField('text');
        $fieldset->removeField('styles');

        $fieldset->addType(
            'preview',
            '\Magetrend\NewsletterMaker\Block\Adminhtml\Newsletter\Queue\Edit\Renderer\Preview'
        );

        $fieldset->addField(
            'preview',
            'preview',
            [
                'name'  => 'preview',
                'label' => __('Preview'),
                'title' => __('Preview'),
            ]
        );

        $fieldset->addField(
            'is_mtemail',
            'hidden',
            [
                'name'  => 'is_mtemail',
                'value' => 1
            ]
        );

        $fieldset->addField(
            'text',
            'hidden',
            [
                'name'  => 'text',
                'value' => $this->templateHelper->cleanTemplate($queue->getTemplate()->getTemplateText())
            ]
        );

        $fieldset->addField(
            'styles',
            'hidden',
            [
                'name'  => 'styles',
                'value' => $queue->getTemplate()->getTemplateStyles()
            ]
        );

        return $mainObject;
    }
}
