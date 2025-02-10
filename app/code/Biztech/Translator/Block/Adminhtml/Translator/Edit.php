<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Translator;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Block\Widget\Context;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @param Context                  $context
     * @param ProductMetadataInterface $productMetadataInterface
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadataInterface,
        array $data = []
    ) {
        $this->productMetadataInterface = $productMetadataInterface;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _construct()
    {
        /** @var TYPE_NAME $this */
        $this->_objectId = 'biztech_translator_tabs';
        $this->_blockGroup = 'Biztech_Translator';
        $this->_controller = 'adminhtml_translator';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
         
        $version = $this->productMetadataInterface->getVersion();
            
        if (version_compare($version, '2.1', '<')) {
            $requirejs = 'biztechTranslator';
        } else {
            $requirejs = 'biztechTranslatorv213';
        }

        $this->_formScripts[] = '
        require([
            "jquery",
           "'.$requirejs.'"
        ], function($,biztechTranslator){

        });
        ';
    }

    public function getHeaderText()
    {
        return __('Edit Translation String');
    }
}
