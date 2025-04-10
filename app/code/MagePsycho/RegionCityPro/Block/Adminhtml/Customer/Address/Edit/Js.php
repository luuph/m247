<?php

namespace MagePsycho\RegionCityPro\Block\Adminhtml\Customer\Address\Edit;

use Magento\Backend\Block\Template;
use Magento\Framework\View\LayoutInterface;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Helper\City as CityHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Js extends Template
{
    private const CUSTOMER_ADDRESS_EDIT_BLOCK_NAME = 'customer_address_edit';

    /**
     * @var LayoutInterface
     */
    private $currentLayout;

    /**
     * @var RegionCityProHelper
     */
    private $regionCityProHelper;

    /**
     * @var CityHelper
     */
    private $cityHelper;

    public function __construct(
        Template\Context $context,
        RegionCityProHelper $regionCityProHelper,
        CityHelper $cityHelper,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->regionCityProHelper = $regionCityProHelper;
        $this->currentLayout = $context->getLayout();
        $this->cityHelper = $cityHelper;
    }

    public function toHtml()
    {
        if ($this->regionCityProHelper->isFxnSkipped()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function isActive()
    {
        return !$this->regionCityProHelper->isFxnSkipped();
    }

    public function getCityJson()
    {
        return $this->cityHelper->getJsonData();
    }
}
