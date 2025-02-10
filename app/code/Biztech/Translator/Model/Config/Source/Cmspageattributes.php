<?php

/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. * */

namespace Biztech\Translator\Model\Config\Source;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Eav\Model\Config;

class Cmspageattributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $productAttributes;
    protected $eavConfig;
    protected $productMetadataInterface;

    /**
     * @param ProductMetadataInterface $productMetadataInterface [description]
     * @param Product                  $productAttributes        [description]
     * @param Config                   $eavConfig                [description]
     */
    public function __construct(
        ProductMetadataInterface $productMetadataInterface,
        Product $productAttributes,
        Config $eavConfig
    ) {
        $this->productMetadataInterface = $productMetadataInterface;
        $this->productAttributes = $productAttributes;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $version = $this->productMetadataInterface->getVersion();
        $lower_version = 0;
        if (version_compare($version, '2.1', '<')) {
            $new_array = [
                ["label" => "Page Title", "value" => "page_title"],
                ["label" => "Content Heading", "value" => "page_content_heading"],
                ["label" => "Content", "value" => "page_content"],
                ["label" => "Meta Keywords", "value" => "page_meta_keywords"],
                ["label" => "Meta Description", "value" => "page_meta_description"]
            ];
        } else {
            $new_array = [
                ["label" => "Page Title", "value" => "title"],
                ["label" => "Content Heading", "value" => "content_heading"],
                ["label" => "Content", "value" => "content"],
                ["label" => "Meta Title", "value" => "meta_title"],
                ["label" => "Meta Keywords", "value" => "meta_keywords"],
                ["label" => "Meta Description", "value" => "meta_description"]
            ];
        }

        return $new_array;
    }
}
