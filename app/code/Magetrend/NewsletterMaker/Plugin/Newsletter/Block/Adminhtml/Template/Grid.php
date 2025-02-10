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

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Block\Adminhtml\Template;

class Grid
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
     * Returns grid row url
     *
     * @param $subject
     * @param $process
     * @param $row
     * @return mixed
     */
    public function aroundGetRowUrl($subject, callable $process, $row)
    {
        if ($row->getIsMtemail()) {
            return $subject->getUrl('newslettermaker/mteditor/index', ['id' => $row->getId()]);
        }

        return $process($row);
    }
}
