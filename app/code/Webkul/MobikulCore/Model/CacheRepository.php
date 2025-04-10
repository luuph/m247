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

namespace Webkul\MobikulCore\Model;

use Webkul\MobikulCore\Api\Data\CacheInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CacheRepository model
 */
class CacheRepository implements \Webkul\MobikulCore\Api\CacheRepositoryInterface
{
    /**
     * ResourceModel variable
     *
     * @var ResourceModel\Cache
     */
    protected $_resourceModel;
    
    /**
     * Instances variable
     *
     * @var array
     */
    protected $_instances = [];
    
    /**
     * CarouselFactory variable
     *
     * @var mixed
     */
    protected $_carouselFactory;
    
    /**
     * CollectionFactory variable
     *
     * @var ResourceModel\Cache\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * CacheFactory variable
     *
     * @var CacheFactory
     */
    protected $_cacheFactory;
    
    /**
     * InstancesById variable
     *
     * @var array
     */
    protected $_instancesById = [];

    /**
     * Construct function
     *
     * @param CacheFactory $cacheFactory
     * @param ResourceModel\Cache $resourceModel
     * @param ResourceModel\Cache\CollectionFactory $collectionFactory
     */
    public function __construct(
        CacheFactory $cacheFactory,
        ResourceModel\Cache $resourceModel,
        ResourceModel\Cache\CollectionFactory $collectionFactory
    ) {
        $this->_cacheFactory = $cacheFactory;
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Save function
     *
     * @param CacheInterface $cache
     * @return void
     */
    public function save(CacheInterface $cache)
    {
        $cacheId = $cache->getId();
        try {
            $this->_resourceModel->save($cache);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$cache->getId()]);
        return $this->getById($cache->getId());
    }

    /**
     * GetById function
     *
     * @param int $cacheId
     * @return void
     */
    public function getById($cacheId)
    {
        $cacheData = $this->_cacheFactory->create();
        $cacheData->load($cacheId);
        $this->_instancesById[$cacheId] = $cacheData;
        return $this->_instancesById[$cacheId];
    }

    /**
     * GetList function
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return void
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    /**
     * Delete function
     *
     * @param CacheInterface $cache
     * @return void
     */
    public function delete(CacheInterface $cache)
    {
        $cacheId = $cache->getId();
        try {
            $this->_resourceModel->delete($cache);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(__("Unable to remove cache with id %1", $cacheId));
        }
        unset($this->_instancesById[$cacheId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $cacheId
     * @return void
     */
    public function deleteById($cacheId)
    {
        $cache = $this->getById($cacheId);
        return $this->delete($cache);
    }
}
