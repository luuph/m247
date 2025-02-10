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

namespace Magetrend\NewsletterMaker\Plugin\Email\Model;

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
     * Template constructor.
     * @param \Magetrend\NewsletterMaker\Helper\Template $templateHelper
     */
    public function __construct(
        \Magetrend\NewsletterMaker\Helper\Template $templateHelper,
        \Magetrend\NewsletterMaker\Helper\Variables $variablesHelper
    ) {
        $this->templateHelper = $templateHelper;
        $this->variablesHelper = $variablesHelper;
    }

    /**
     * Fix images links
     *
     * @param $template
     * @param callable $proceed
     * @param array $variables
     * @return mixed
     */
    public function aroundGetProcessedTemplate($template, callable $proceed, array $variables = [])
    {
        $variables['mt'] = $this->variablesHelper;
        return $this->templateHelper->replaceImageUrl($template, $proceed($variables), $variables);
    }
}
