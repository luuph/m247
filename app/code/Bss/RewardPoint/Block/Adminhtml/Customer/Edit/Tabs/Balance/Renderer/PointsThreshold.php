<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Renderer;

use Bss\RewardPoint\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;

/**
 * Class points threshold
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Renderer
 */
class PointsThreshold extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * PointsThreshold constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Render
     *
     * @param DataObject $row
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        $websiteId = $row->getWebsiteId();
        return $this->helper->getPointsThreshold($websiteId);
    }
}
