<?php
/**
 * @category Mageants StorePickup
 * @package Mageants_StorePickup
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\StoreLocator\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
/**
 * Class Locations Backend system config array field renderer
 */
class Locations extends AbstractFieldArray
{
    /**
     * Initialise columns for 'Store Locations'
     * Label is name of field
     * Class is storefront validation action for field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn(
            'title',
            [
                'label' => __('Store Name'),
                'class' => 'validate-no-empty validate-alphanum-with-spaces',
                'style' => 'width:150px'
            ]
        );
        $this->addColumn(
            'street',
            [
                'label' => __('Street Address'),
                'class' => 'validate-no-empty validate-street',
                'style' => 'width:150px'
            ]
        );
        $this->addColumn(
            'phone',
            [
                'label' => __('Phone Number'),
                'class' => 'validate-no-empty validate-number',
                'style' => 'width:150px'
            ]
        );

        $this->_addAfter = false;
        parent::_construct();
    }
    
    /* public function renderCellTemplate($columnName)
		{
			/* if ($columnName == "street") {
				$this->_columns[$columnName]['class'] = 'input-text required-entry';
				//$this->_columns[$columnName]['style'] = 'width:50px';
				$this->_columns[$columnName]['renderer'] = '';
			} */
            //return parent::renderCellTemplate($columnName);
        //}
        
}
