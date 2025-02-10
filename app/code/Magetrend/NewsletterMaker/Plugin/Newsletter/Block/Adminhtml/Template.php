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

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Block\Adminhtml;

class Template
{
    /**
     * @var \Magetrend\NewsletterMaker\Helper\Data
     */
    public $mtHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * GridPlugin constructor.
     * @param  \Magento\Framework\Registry $registry
     * @param \Magetrend\NewsletterMaker\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\NewsletterMaker\Helper\Data $helper
    ) {
        $this->mtHelper = $helper;
        $this->registry = $registry;
    }

    /**
     * Update email template grid
     * There is no another possibility to add
     * @param Template $template
     */
    public function beforeGetCreateUrl(\Magento\Newsletter\Block\Adminhtml\Template $template)
    {
        if ($this->registry->registry('newslettermaker_button_added') != 1) {
            $template->getToolbar()->addChild(
                'newletter_maker',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Newsletter Maker'),
                    'onclick' => "window.location='" . $template->getUrl('newslettermaker/mteditor/index') . "'",
                    'class' => 'add primary add-template'
                ]
            );

            $this->registry->register('newslettermaker_button_added', 1);
        }
    }
}
