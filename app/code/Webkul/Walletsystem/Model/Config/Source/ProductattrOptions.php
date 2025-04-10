<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
use Webkul\Walletsystem\Model\Walletcreditrules;

/**
 * Webkul Walletsystem Creditrules Class
 */
class ProductattrOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var Array
     */
    public $options;
    
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->options=[
            [
                'label'=>'Select Options',
                'value'=>''
            ],[
                'label'=>__('Product Credit Amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_PRODUCT
            ],[
                'label'=>__('Rules amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_RULE
            ]
        ];
        return $this->options;
    }

    /**
     * Get a text for option value
     *
     * @param array $value
     * @return boolean
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}
