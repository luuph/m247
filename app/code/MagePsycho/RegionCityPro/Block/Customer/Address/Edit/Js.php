<?php

namespace MagePsycho\RegionCityPro\Block\Customer\Address\Edit;

use Magento\Framework\View\Element\Template;
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
        $this->currentLayout       = $context->getLayout();
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
        return $this->regionCityProHelper->getConfig()->isEnabled();
    }

    public function isCountrySearchable()
    {
        return $this->regionCityProHelper->getConfig()->isCountrySearchable();
    }

    public function isRegionSearchable()
    {
        return $this->regionCityProHelper->getConfig()->isRegionSearchable();
    }

    public function isCitySearchable()
    {
        return $this->regionCityProHelper->getConfig()->isCitySearchable();
    }

    public function getCityJson()
    {
        return $this->cityHelper->getJsonData();
    }

    private function getCurrentAddress()
    {
        $customerAddressBlock = $this->currentLayout->getBlock(self::CUSTOMER_ADDRESS_EDIT_BLOCK_NAME);
        if (! $customerAddressBlock) {
            return false;
        }
        return $customerAddressBlock->getAddress();
    }

    public function getCityId()
    {
        $customerAddress = $this->getCurrentAddress();
        if (! $customerAddress || ! $customerAddress->getId()) {
            return 0;
        }

        return $customerAddress->getCustomAttribute('city_id')
            ? $customerAddress->getCustomAttribute('city_id')->getValue()
            : 0;
    }
}
