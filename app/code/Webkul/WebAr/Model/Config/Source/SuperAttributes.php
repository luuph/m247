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
namespace Webkul\WebAr\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class SuperAttributes extends AbstractSource
{
    /**
     * @var array|null
     */
    protected $_options = null;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    protected $attrHandler;

    /**
     * Initialize dependencies
     *
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $attrHandler
     * @return void
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $attrHandler
    ) {
        $this->attrHandler = $attrHandler;
    }

    /**
     * Get Super Attributes Options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collection =  $this->attrHandler->getApplicableAttributes();

        $items = [];
        $collection->getSelect()->where(
            '(`apply_to` IS NULL) OR
            (
                FIND_IN_SET(' .
            sprintf("'%s'", Type::TYPE_SIMPLE) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
            sprintf("'%s'", Type::TYPE_VIRTUAL) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
            sprintf("'%s'", Configurable::TYPE_CODE) . ',
                    `apply_to`
                )
             )'
        );
        foreach ($collection->getItems() as $attribute) {
            $items[] = $attribute->toArray();
        }
       
        if (null === $this->_options) {
            $this->_options[] = [
                'value' => "",
                'label' => __("Please choose attribute")
            ];
            foreach ($items as $index => $attributeData) {
                $this->_options[] = [
                    'value' => $attributeData['attribute_id'] ?? 0,
                    'label' => $attributeData['frontend_label'] ?? ""
                ];
            }
        }

        return $this->_options;
    }
}
