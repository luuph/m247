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
class CouponCode implements \Magento\Framework\Option\ArrayInterface
{
    protected $_salesRuleCoupon;
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_ruleFactory;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $salesRuleCoupon)
    {
        $this->_salesRuleCoupon = $salesRuleCoupon;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options=[];
        $model=$this->_salesRuleCoupon->create();
        $catalogRule=$model->getData();
        $count=0;
        foreach ($catalogRule as $value) {
             $options[$count] = [
                'label' => $value['code'],
                'value' => $value['coupon_id']
             ];
             $count++;
        }
        return $options;
    }
}
