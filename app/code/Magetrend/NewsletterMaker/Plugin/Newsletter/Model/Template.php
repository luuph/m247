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

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Model;

use Magetrend\NewsletterMaker\Helper\Variables;

class Template
{
    /**
     * @var \Magetrend\NewsletterMaker\Helper\Template
     */
    public $templateHelper;

    /**
     * @var \Magetrend\NewsletterMaker\Helper\Variables
     */
    public $variablesHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Template constructor.
     *
     * @param \Magetrend\NewsletterMaker\Helper\Template $templateHelper
     * @param Variables $variablesHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magetrend\NewsletterMaker\Helper\Template $templateHelper,
        \Magetrend\NewsletterMaker\Helper\Variables $variablesHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->variablesHelper = $variablesHelper;
        $this->templateHelper = $templateHelper;
        $this->registry = $registry;
    }

    /**
     * Render newsletter template
     *
     * @param $template
     * @param callable $proceed
     * @param array $variables
     * @return mixed
     */
    public function aroundGetProcessedTemplate($template, callable $proceed, array $variables = [])
    {
        if ($template->getId()) {
            $this->registry->unregister(Variables::REGISTRY_NEWSLETTER_ID);
            $this->registry->register(Variables::REGISTRY_NEWSLETTER_ID, $template->getId());
        }
        $variables['mt'] = $this->variablesHelper;
        return $this->templateHelper->replaceImageUrl($template, $proceed($variables), $variables);
    }
}
