<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
 
class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Eav\Model\Entity\Attribute $eavAttribute
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity\Attribute $eavAttribute
    ) {
        $this->request = $request;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * Execute method to check model variant attribute value
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            $attrId = $product->getModelVariantAttribute();
            $attributesReq = $this->request->getPostValue("attributes", []);

            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                if ($attrId == "" || $attrId == "0" || $attrId == 0) {
                    throw new LocalizedException(__('Please choose Model Variant Attribute'));
                }
                
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($product->getStoreId(), $product);

                $attributes = $productTypeInstance->getConfigurableAttributes($product);
                $superAttributeList = [];
                foreach ($attributes as $attribute) {
                    $attributeLabel = $attribute->getLabel();
                    $superAttributeList[$attribute->getAttributeId()] = $attributeLabel;
                }

                $superAttributeListIds = array_keys($superAttributeList);
                $superAttributeLabels = array_values($superAttributeList);
                $superAttributeLabelsStr = !empty($superAttributeLabels)?implode(', ', $superAttributeLabels ?? ""):"";
                
                if ($superAttributeLabelsStr == "" && !empty($attributesReq)) {
                    $attributeLabels = $this->eavAttribute->getCollection()
                        ->addFieldToFilter("attribute_id", ["in"=>$attributesReq])
                        ->getColumnValues("frontend_label");

                    $superAttributeLabelsStr = !empty($attributeLabels)?implode(', ', $attributeLabels ?? ""):"";
                    $superAttributeListIds = $attributesReq;
                }
                
                if (!(in_array((int)$attrId, $superAttributeListIds))) {
                    throw new LocalizedException(__(
                        "Model Variant Attribute must be one of used super attributes: %1",
                        $superAttributeLabelsStr
                    ));
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__(
                $e->getMessage()
            ));
        }
    }
}
