<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\Config\Source;

class Productattributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $productAttributes;
    protected $storeManager;
    protected $eavcollection;
    protected $eavConfig;

    /**
     * @param \Magento\Catalog\Model\Product                               $productAttributes [description]
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavcollection     [description]
     * @param \Magento\Eav\Model\Config                                    $eavConfig         [description]
     * @param \Biztech\Translator\Model\Config                             $config            [description]
     */
    public function __construct(
        \Magento\Catalog\Model\Product $productAttributes,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavcollection,
        \Magento\Eav\Model\Config $eavConfig,
        \Biztech\Translator\Model\Config $config
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavcollection = $eavcollection;
        $this->productAttributes = $productAttributes->getAttributes();
        $this->storeManager = $config->getStoreManager();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $excludedAttributes = $this->getExcludedAttributes();
        $allAttributes = [];

        $entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getEntityTypeId();
        $this->eavcollection->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, $entityTypeId);
        $attrAll = $this->eavcollection->load()->getItems();

        foreach ($attrAll as $productAttribute) {
            if (($productAttribute->getFrontendInput() == 'textarea' || $productAttribute->getFrontendInput() == 'text') && (!in_array($productAttribute->getAttributeCode(), $excludedAttributes))) {
                $allAttributes[] = ['label' => $productAttribute->getAttributeCode(), 'value' => $productAttribute->getAttributeCode()];
            }
        }
        return $allAttributes;
    }

    /**
     * @return array
     */
    public function getExcludedAttributes()
    {
        $attributes = ['sku', 'required_options', 'has_options', 'created_at', 'updated_at', 'group_price', 'tier_price', 'custom_layout_update', 'old_id', 'url_path', 'category_ids'];
        return $attributes;
    }
}
