<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties;

/**
 * Class Widget
 * @package Mageplaza\Shopbybrand\Plugin\Block\Adminhtml
 */
class Widget
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * Widget constructor.
     *
     * @param RequestInterface $request
     * @param Json $json
     * @param Yesno $yesno
     */
    public function __construct(
        RequestInterface $request,
        Json $json,
        Yesno $yesno
    ) {
        $this->request = $request;
        $this->json    = $json;
        $this->yesno   = $yesno;
    }

    /**
     * @param Properties $subject
     * @param $result
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function afterGetMainFieldset(Properties $subject, $result)
    {
        if ($this->request->getParam('code') == 'mpbrand_advanced_widget' && !$subject->getForm()->getElement('mp_shopbybrand_widget_design')) {
            $designFieldset = $subject->getForm()->addFieldset(
                'mp_shopbybrand_widget_design',
                ['legend' => __('Widget Brand Design'), 'class' => 'fieldset-wide fieldset-widget-options']
            );

            $displayStyle   = $designFieldset->addField('display_style', 'select', [
                'name'   => 'display_style',
                'label'  => __('Display Style'),
                'title'  => __('Display Style'),
                'values' => [
                    ['value' => '0', 'label' => __('Slider')],
                    ['value' => '1', 'label' => __('List View')],
                ]
            ]);
            $sliderWidth    = $designFieldset->addField('slider_width', 'text', [
                'name'  => 'slider_width',
                'class' => 'validate-greater-than-zero validate-number',
                'label' => __('Slider Width (px)'),
                'title' => __('Slider Width (px)')
            ]);
            $sliderHeight   = $designFieldset->addField('slider_height', 'text', [
                'name'  => 'slider_height',
                'class' => 'validate-greater-than-zero validate-number',
                'label' => __('Slider Height (px)'),
                'title' => __('Slider Height (px)')
            ]);
            $nextPrevButton = $designFieldset->addField('next_prev_button', 'select', [
                'name'   => 'next_prev_button',
                'label'  => __('Show Next/Prev Buttons'),
                'title'  => __('Show Next/Prev Buttons'),
                'values' => $this->yesno->toOptionArray(),
                'note'   => __('If Yes, multiple brands will be displayed in one slider, and Next/Prev buttons will be placed next to that slider.')
            ]);
            $showDotsNav    = $designFieldset->addField('show_dots_nav', 'select', [
                'name'   => 'show_dots_nav',
                'label'  => __('Show Dots Navigation'),
                'title'  => __('Show Dots Navigation'),
                'values' => $this->yesno->toOptionArray(),
                'note'   => __('If Yes, multiple brands will be displayed in one slider, and Dots Navigation will be displayed with that slider.')
            ]);
            $autoPlay       = $designFieldset->addField('auto_play', 'select', [
                'name'   => 'auto_play',
                'label'  => __('Auto Play'),
                'title'  => __('Auto Play'),
                'values' => $this->yesno->toOptionArray(),
                'note'   => __('Select yes to allow next brand to be auto-displayed.')
            ]);
            $autoTimeout    = $designFieldset->addField('auto_timeout', 'text', [
                'name'  => 'auto_timeout',
                'class' => 'integer validate-greater-than-zero',
                'label' => __('Auto Time-out (ms)'),
                'title' => __('Auto Time-out (ms)'),
                'note'  => __('If empty or 0, will default to 2000 ms.')
            ]);

            $designFieldset->addField('limit_brands', 'text', [
                'name'  => 'limit_brands',
                'class' => 'integer validate-greater-than-zero',
                'label' => __('Limit Number of Brands'),
                'title' => __('Limit Number of Brands')
            ]);

            $form = $subject->getForm()->setValues($this->getFieldData($subject->getWidgetInstance()->getData('widget_parameters')));
            $subject->setForm($form);

            $block = $subject->getLayout()->createBlock(Dependence::class);
            $block->addFieldMap($displayStyle->getHtmlId(), $displayStyle->getName())
                ->addFieldMap($sliderWidth->getHtmlId(), $sliderWidth->getName())
                ->addFieldMap($sliderHeight->getHtmlId(), $sliderHeight->getName())
                ->addFieldMap($nextPrevButton->getHtmlId(), $nextPrevButton->getName())
                ->addFieldMap($showDotsNav->getHtmlId(), $showDotsNav->getName())
                ->addFieldMap($autoPlay->getHtmlId(), $autoPlay->getName())
                ->addFieldMap($autoTimeout->getHtmlId(), $autoTimeout->getName())
                ->addFieldDependence($sliderWidth->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($sliderHeight->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($nextPrevButton->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($showDotsNav->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($autoPlay->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($autoTimeout->getName(), $displayStyle->getName(), 0)
                ->addFieldDependence($autoTimeout->getName(), $autoPlay->getName(), 1);

            $subject->setChild('form_after', $block);
        }

        return $result;
    }

    /**
     * @param $parameter
     *
     * @return array|bool|float|int|int[]|mixed|string|null
     */
    protected function getFieldData($parameter)
    {
        $widgetParameters = [
            'auto_play'        => 1,
            'next_prev_button' => 1,
            'show_dots_nav'    => 1
        ];
        if ($parameter) {
            $widgetParameters = $this->json->unserialize($parameter);
            if (array_key_exists('display_style', $widgetParameters) && $widgetParameters['display_style'] == 1) {
                $widgetParameters['auto_play']        = 1;
                $widgetParameters['next_prev_button'] = 1;
                $widgetParameters['show_dots_nav']    = 1;
            }
        }

        return $widgetParameters;
    }
}
