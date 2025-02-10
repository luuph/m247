<?php
/**
 * FME Restrict Payment Method  Model Config Source Options.
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @author    Adeel Anjum
 * @copyright Copyright (c) 2018 United Sol Private Limited (https://unitedsol.net)
 */
namespace FME\RestrictPaymentMethod\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class CatalogRule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_ruleFactory;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\CatalogRule\Model\RuleFactory $ruleFactory)
    {
        $this->_ruleFactory = $ruleFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options=[];
        $model=$this->_ruleFactory->create();
        $catalogRule=$model->getCollection();
        $count=0;
        foreach ($catalogRule as $value) {
             $options[$count] = [
                'label' => $value->getName(),
                'value' => $value->getId()
             ];
             $count++;
        }
        return $options;
    }
}
