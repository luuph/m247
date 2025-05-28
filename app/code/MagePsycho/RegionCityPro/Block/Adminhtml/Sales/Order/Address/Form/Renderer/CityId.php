<?php

namespace MagePsycho\RegionCityPro\Block\Adminhtml\Sales\Order\Address\Form\Renderer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Helper\City as CityHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CityId extends AbstractBlock implements RendererInterface
{
    /**
     * @var RegionCityProHelper
     */
    private $regionCityProHelper;

    /**
     * @var CityHelper
     */
    private $cityHelper;

    public function __construct(
        Context $context,
        RegionCityProHelper $regionCityProHelper,
        CityHelper $cityHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->regionCityProHelper = $regionCityProHelper;
        $this->cityHelper = $cityHelper;
    }

    /**
     * Render form element as HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (! ($country = $element->getForm()->getElement('country_id'))
            || ! ($region = $element->getForm()->getElement('region_id'))
        ) {
            return $element->getDefaultHtml();
        }

        $cityId = $element->getForm()->getElement('city_id')->getValue();

        $html = '<div class="field field-state required admin__field _required">';
        $element->setClass('input-text admin__control-text');
        $element->setRequired(true);
        $html .= $element->getLabelHtml() . '<div class="control admin__field-control">';
        $html .= $element->getElementHtml();

        $selectName = str_replace('city', 'city_id', $element->getName());
        $selectId = $element->getHtmlId() . '_id';
        $html .= '<select id="' .
            $selectId .
            '" name="' .
            $selectName .
            '" class="select required-entry admin__control-select" style="display:none">';
        $html .= '<option value="">' . __('Please select') . '</option>';
        $html .= '</select>';

        $html .= '<script>' . "\n";
        $html .= 'require([';
        $html .= '"prototype", "mage/adminhtml/form", "MagePsycho_RegionCityPro/js/city-updater"';
        $html .= '], function() {';
        $html .= '$("' . $selectId . '").setAttribute("defaultValue", "' . $cityId . '");' . "\n";
        $html .= 'new CityUpdater("' .
            $country->getHtmlId() .
            '", "' .
            $region->getHtmlId() .
            '", "' .
            $element->getForm()->getElement('region')->getHtmlId() .
            '", "' .
            $element->getHtmlId() .
            '", "' .
            $selectId .
            '", ' .
            $this->cityHelper->getJsonData() .
            ');' .
            "\n";
        $html .= '});';
        $html .= '</script>' . "\n";
        $html .= '</div></div>' . "\n";

        return $html;
    }
}
