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

namespace Mageplaza\Shopbybrand\Block\Widget\Advanced;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Block\Adminhtml\Widget\Chooser;
use Magento\Widget\Model\Widget\Instance;
use Mageplaza\Shopbybrand\Block\Adminhtml\Related\Edit\Tab\Renderer\Store;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class BrandList
 * @package Mageplaza\Shopbybrand\Block\Widget\Advanced
 */
class BrandList extends Extended
{
    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var Instance
     */
    protected $widgetInstance;

    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * BrandList constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param BrandFactory $brandFactory
     * @param Instance $widgetInstance
     * @param Json $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        BrandFactory $brandFactory,
        Instance $widgetInstance,
        Json $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->brandFactory   = $brandFactory;
        $this->widgetInstance = $widgetInstance;
        $this->jsonHelper     = $jsonHelper;
    }

    /**
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brand_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId    = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'mpbrand/widget/brandList',
            ['uniq_id' => $uniqId]
        );

        $chooser = $this->getLayout()->createBlock(
            Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $elementValue = explode(',', $element->getValue());
            $optionArray  = $this->getBrandOptionArray();
            $label        = [];

            foreach ($elementValue as $value) {
                if (array_key_exists($value, $optionArray)) {
                    $label[] = $optionArray[$value];
                }
            }
            $label = implode(',', $label);
            if (strlen($label) > 50) {
                $label = substr($label, 0, 50) . '...';
            }

            $chooser->setLabel($label);
        }

        $element->setData('after_element_html', $chooser->toHtml());

        return $element;
    }

    /**
     * @return BrandList
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        $brandCollection = $this->brandFactory->create()->getBrandCollection();
        $this->setCollection($brandCollection);

        return parent::_prepareCollection();
    }

    /**
     * @return BrandList
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('brand_name', [
            'header'   => __('Brand Name'),
            'sortable' => true,
            'index'    => 'value',
            'type'     => 'text'
        ]);

        $this->addColumn('brand_id', [
            'header'           => __('Option ID'),
            'sortable'         => true,
            'index'            => 'option_id',
            'type'             => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);

        $this->addColumn('featured', [
            'header'   => __("Featured"),
            'width'    => '50px',
            'align'    => 'right',
            'index'    => 'is_featured',
            'type'     => 'options',
            'options'  => ['1' => 'Enabled', '0' => 'Disabled'],
            'editable' => false,
        ]);

        $this->addColumn('store_id', [
            'header'           => __('Store view'),
            'index'            => 'store_id',
            'sortable'         => false,
            'filter'           => false,
            'column_css_class' => 'admin__scope-old',
            'renderer'         => Store::class,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getBrandOptionArray()
    {
        $brandCollection = $this->brandFactory->create()->getBrandCollection();
        $optionArray     = [];

        foreach ($brandCollection as $brand) {
            $optionArray[$brand->getOptionId()] = $brand->getValue();
        }

        return $optionArray;
    }

    /**
     * @return $this|BrandList
     * @throws LocalizedException
     */
    protected function _prepareMassaction()
    {
        $optionArray     = $this->getBrandOptionArray();
        $chooserJsObject = $this->getId();
        if (!$this->_request->getPostValue('internal_option_id')) {
            $widgetParameter = $this->_request->getPost('element_value');
            $this->_request->setPostValue('internal_option_id', $widgetParameter);
        }
        $this->setMassactionIdField('option_id');
        $this->getMassactionBlock()->setFormFieldName('option_id');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->addItem('add_brand', [
            'label'    => __('Add Brands'),
            'url'      => $this->getUrl('mpbrand/widget/addBrand'),
            'complete' => 'var formData = this.form.serialize(true), optionId = formData.option_id,
                selectValue = ' . $this->jsonHelper->serialize($optionArray) . ', label = [];

                optionId.split(",").forEach((value, index) => {
                    if (selectValue[value]) {
                        label.push(selectValue[value]);
                    }
                });
                
                var chooserLabel = label.toString();
                if (chooserLabel.length > 50) {
                    chooserLabel = chooserLabel.slice(0, 50) + "...";
                }'
                . $chooserJsObject . '.setElementValue(optionId);'
                . $chooserJsObject . '.setElementLabel(label.toString());'
                . $chooserJsObject . '.close();'
                . 'var instantiateChooser = function() {
                        window.' . $chooserJsObject .
                ' = new WysiwygWidget.chooser(
                            "' .
                $chooserJsObject .
                '",
                            "' .
                $this->getUrl(
                    'mpbrand/widget/brandList',
                    ['uniq_id' => $chooserJsObject]
                ) .
                '",
                            ' .
                '{"buttons":{"open":"Select Brand List","close":"Close"}}' .
                '
                        );
                        if ($("' .
                $chooserJsObject .
                'value")) {
                            $("' .
                $chooserJsObject .
                'value").advaiceContainer = "' .
                $chooserJsObject .
                'advice-container";
                        }
                    }
                jQuery(instantiateChooser);'
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpbrand/widget/brandList', ['_current' => true]);
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * @param Column $column
     *
     * @return $this|BrandList
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        $brandCollection = $this->getCollection();

        if ($column->getId() == 'massaction') {
            if ($column->getFilter()->getValue() == 1) {
                $optionId = $this->_request->getParam('internal_option_id');
                $brandCollection->addFieldToFilter('main_table.option_id', ['in' => explode(',', $optionId)]);
            }
            if ($column->getFilter()->getValue() == 0) {
                $optionId = $this->_request->getParam('internal_option_id');
                $brandCollection->addFieldToFilter('main_table.option_id', ['nin' => explode(',', $optionId)]);
            }
        }

        if ($column->getId() == 'brand_id') {
            if ($column->getFilter()->getValue()) {
                $value = $column->getFilter()->getValue();
                if ($value && isset($value['from'])) {
                    $brandCollection->addFieldToFilter('main_table.option_id', ['gteq' => $value['from']]);
                }
                if ($value && isset($value['to'])) {
                    $brandCollection->addFieldToFilter('main_table.option_id', ['lteq' => $value['to']]);
                }
            }
        }

        if ($column->getId() == 'brand_name') {
            if ($column->getFilter()->getValue()) {
                $value = $column->getFilter()->getValue();
                $brandCollection->addFieldToFilter(
                    ['tsv.value', 'tdv.value'],
                    [
                        ['like' => '%' . $value . '%'],
                        ['like' => '%' . $value . '%']
                    ]
                );
            }
        }

        if ($column->getId() == 'featured') {
            if ($column->getFilter()->getValue() != null) {
                $value = $column->getFilter()->getValue();
                $brandCollection->addFieldToFilter('br.is_featured', ['eq' => $value]);
            }
        }

        $this->setCollection($brandCollection);

        return $this;
    }

    /**
     * @param Column $column
     *
     * @return $this|BrandList
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->reset('order')->order($columnIndex . ' ' . strtoupper($column->getDir()));
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getStoreOptionArray()
    {
        $stores      = $this->_storeManager->getStores();
        $optionArray = [];
        foreach ($stores as $store) {
            $optionArray[$store->getId()] = $store->getName();
        }

        return $optionArray;
    }
}
