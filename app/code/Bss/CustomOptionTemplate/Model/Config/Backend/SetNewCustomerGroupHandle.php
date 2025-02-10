<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\Config\Backend;

use Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class SetNewCustomerGroupHandle extends \Magento\Framework\App\Config\Value
{
    /**
     * @var OptionVisibleGroupCustomer
     */
    private $optionVisibleGroupCustomer;

    /**
     * SetNewCustomerGroupHandle constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param OptionVisibleGroupCustomer $optionVisibleGroupCustomer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        OptionVisibleGroupCustomer $optionVisibleGroupCustomer,
        array $data = []
    ) {
        $this->optionVisibleGroupCustomer = $optionVisibleGroupCustomer;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     */
    public function beforeSave()
    {
        /* @var string $value */
        $value = $this->getValue();
        if ($value != '') {
            $this->optionVisibleGroupCustomer->updateNewCustomerGroupForOptions($value);
        }
        $this->setValue('');
        return parent::beforeSave();
    }
}
