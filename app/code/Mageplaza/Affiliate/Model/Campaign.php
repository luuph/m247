<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Model;

use Magento\CatalogRule\Model\Rule\Condition\Combine as CatalogCombine;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as ConProdCombineFactory;
use Mageplaza\Affiliate\Model\ResourceModel\Campaign as ResourceModel;
use Mageplaza\Affiliate\Model\Rule\Condition\CombineFactory as CombineAffiliateFactory;

/**
 * Class Campaign
 * @package Mageplaza\Affiliate\Model
 * @method setCommission(array $unserialize)
 */
class Campaign extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'affiliate_campaign';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'affiliate_campaign';

    /**
     * @var string
     */
    protected $_eventPrefix = 'affiliate_campaign';

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = [];

    /**
     * @var CombineAffiliateFactory
     */
    protected $condCombineFactory;

    /**
     * @var ConProdCombineFactory
     */
    protected $condProdCombineFactory;

    /**
     * Campaign constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineAffiliateFactory $condCombineFactory
     * @param ConProdCombineFactory $condProdCombineFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineAffiliateFactory $condCombineFactory,
        ConProdCombineFactory $condProdCombineFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineFactory = $condProdCombineFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get action instance
     *
     * @return Collection|AbstractModel\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(CatalogCombine::class);
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    /**
     * @param $item
     *
     * @return bool
     */
    public function validateActionsRule($item)
    {
        if (!$this->getActions()->validate($item)) {
            $childItems = $item->getChildren();
            $isContinue = true;
            if (!empty($childItems)) {
                foreach ($childItems as $childItem) {
                    if ($this->getActions()->validate($childItem)) {
                        $isContinue = false;
                    }
                }
            }
            if ($isContinue) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCommission()
    {
        $commissionArr = explode('},' ,$this->getData('commission'));
        $tierOneCommission = strpos($this->getData('commission'), '},') == true ? $commissionArr[0] . '}}' : $commissionArr[0];

        return $tierOneCommission;
    }
}
