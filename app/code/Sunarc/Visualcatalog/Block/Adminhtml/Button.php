<?php

namespace Sunarc\Visualcatalog\Block\Adminhtml;

class Button extends \Magento\Backend\Block\Template
{

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $id = $this->_request->getParam('id');
        $addUrl = $this->getUrl("visualcatalog/catalog/index", ['_current' => false, 'id' => $id, '_query' => false]);

        $this->addChild(
            'add_manage_product_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Manage Product Position'),
                'onclick' => "openProductList('" . $addUrl . "', false)",
                'class' => 'add',
                'id' => 'add_manage_category_product_button'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddManageProductHtml()
    {
        return $this->getChildHtml('add_manage_product_button');
    }
}
