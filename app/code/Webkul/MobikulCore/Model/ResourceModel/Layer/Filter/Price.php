<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\ResourceModel\Layer\Filter;

/**
 * Class Price model
 */
class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
{
    /**
     * Layer variable
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layer;
    
    /**
     * Session variable
     *
     * @var \Magento\Customer\Model\Session
     */
    private $session;
    
    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * CustomCollection variable
     *
     * @var Mixed
     */
    public $_customCollection;
    
    public const MIN_POSSIBLE_PRICE = .01;

    /**
     * EventManager variable
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Construct function
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        $connectionName = null
    ) {
        $this->layer = $layerResolver->get();
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->request = $request;
        parent::__construct($context, $eventManager, $layerResolver, $session, $storeManager, null);
    }

    /**
     * GetSelect function
     *
     * @return void
     */
    protected function _getSelect()
    {
        $wholeData = $this->request->getPostValue();
        if (isset($wholeData["custom"]) && $wholeData["customCollection"] == 1) {
            $procollection = $this->_customCollection;
        } else {
            $procollection = $this->layer->getProductCollection();
        }
        $procollection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );
        if ($procollection->getCatalogPreparedSelect() !== null) {
            $selects = clone $procollection->getCatalogPreparedSelect();
        } else {
            $selects = clone $procollection->getSelect();
        }
        // reset columns, order and limitation conditions ///////////////////////////
        $selects->reset(\Magento\Framework\DB\Select::COLUMNS);
        $selects->reset(\Magento\Framework\DB\Select::ORDER);
        $selects->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $selects->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        // remove join with main table //////////////////////////////////////////////
        $fromPart = $selects->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS])
            || !isset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS])
        ) {
            return $selects;
        }
        // processing FROM part /////////////////////////////////////////////////////
        $priceIndexJoinPart = $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinCondition = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = \Magento\Framework\DB\Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]);
        $selects->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $selects->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        // processing WHERE part ////////////////////////////////////////////////////
        $wherePart = $selects->getPart(\Magento\Framework\DB\Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $selects->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
        $excludedJoinPart = \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinCondition as $condition) {
            if (strpos($condition, $excludedJoinPart) !== false) {
                continue;
            }
            $selects->where($this->_replaceTableAlias($condition));
        }
        $selects->where($this->_getPriceExpression($selects) . ' IS NOT NULL');
        return $selects;
    }
}
