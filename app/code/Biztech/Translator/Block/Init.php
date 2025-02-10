<?php

/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Block;

use Magento\Framework\View\Page\Config;
use Magento\Framework\App\ProductMetadataInterface;

class Init extends \Magento\Backend\Block\AbstractBlock
{


    /**
     * @var Magento\Framework\View\Page\Config
     */
    protected $page;
    /**
     * @var Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Config                         $page
     * @param ProductMetadataInterface       $productMetadataInterface
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Config $page,
        ProductMetadataInterface $productMetadataInterface,
        array $data = []
    ) {
        $this->productMetadataInterface = $productMetadataInterface;
        $this->page = $page;
        parent::__construct($context, $data);
    }

    /**
     * @override
     * @see \Magento\Backend\Block\AbstractBlock::_construct()
     * @return void
     */
    protected function _construct()
    {
        $version = $this->productMetadataInterface->getVersion();
        if (version_compare($version, '2.1', '<')) {
            $this->page->addPageAsset('Biztech_Translator::js/jquery/biztechTranslator.js');
        } else {
            $this->page->addPageAsset('Biztech_Translator::js/jquery/biztechTranslatorv213.js');
        }
    }
}
