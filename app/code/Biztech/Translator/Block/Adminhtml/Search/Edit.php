<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Search;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    protected $coreRegistry = null;
    protected $_productMetadataInterface;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface, array $data = [])
    {
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('biztech_search')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($this->coreRegistry->registry('biztech_search')->getTitle()));
        } else {
            return __('New Item');
        }
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()->createBlock('Biztech\Translator\Block\Adminhtml\Search\Edit\Form')->toHtml();
    }

    protected function _construct()
    {
        $this->_objectId = 'search_edit_form';
        $this->_blockGroup = 'Biztech_Translator';
        $this->_controller = 'adminhtml_search';
        $this->_mode = 'edit';

        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Translate String'));
        $this->buttonList->update('delete', 'label', __('Delete Block'));
        
        $version = $this->_productMetadataInterface->getVersion();
            
        if (version_compare($version, '2.1', '<')) {
            $requirejs = 'biztechTranslator';
        } else {
            $requirejs = 'biztechTranslatorv213';
        }
        $this->_formScripts[] = '
		require([
			"jquery",
			"'. $requirejs .'"
		], function(jQuery,biztechTranslator){
			jQuery("#save").click(function(event){
				jQuery("#search_edit_form").submit();
			});
		});
		';
    }
}
