<?php

/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\Config\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ProductMetadataInterface;

class Categoryattributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $productAttributes;
    protected $eavConfig;
    protected $productMetadataInterface;

    /**
     * @param Product                  $productAttributes
     * @param ProductMetadataInterface $productMetadataInterface
     * @param Config                   $eavConfig
     */
    public function __construct(
        Product $productAttributes,
        ProductMetadataInterface $productMetadataInterface,
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
        if (version_compare($version, '2.1', '<')) {
            $new_array = [
                ["label" => "Category Name", "value" => "group_4name"],
                ["label" => "Description", "value" => "group_4description"],
                ["label" => "Meta Keywords", "value" => "group_4meta_keywords"],
                ["label" => "Meta Description", "value" => "group_4meta_description"]
            ];
        } else {
            $new_array = [
                ["label" => "Category Name", "value" => "name"],
                ["label" => "Description", "value" => "description"],
                ["label" => "URL Key", "value" => "url_key"], //Commented as magento not allow to save URL charachers if its like in arabic language
                ["label" => "Meta Title", "value" => "meta_title"],
                ["label" => "Meta Keywords", "value" => "meta_keywords"],
                ["label" => "Meta Description", "value" => "meta_description"],
                ["label" => "Category Seo Name", "value" => "category_seo_name"]
            ];
        }
        return $new_array;
    }
}
