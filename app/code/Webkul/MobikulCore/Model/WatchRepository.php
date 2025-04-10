<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Webkul\MobikulCore\Api\Data\WatchInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class WatchRepository model
 */
class WatchRepository implements \Webkul\MobikulCore\Api\WatchRepositoryInterface
{
    /**
     * ResourceModel Variable
     *
     * @var ResourceModel\Watch
     */
    protected $_resourceModel;
    
    /**
     * Instances Variable
     *
     * @var Array
     */
    protected $_instances = [];
    
    /**
     * WatchFactory Variable
     *
     * @var WatchFactory
     */
    protected $_watchFactory;
    
    /**
     * CollectionFactory Variable
     *
     * @var ResourceModel\Watch\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * InstancesById Variable
     *
     * @var Array
     */
    protected $_instancesById = [];
    
    /**
     * ExtensibleDataObjectConverter Variable
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Construct function
     *
     * @param WatchFactory $watchFactory
     * @param ResourceModel\Watch $resourceModel
     * @param ResourceModel\Watch\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        WatchFactory $watchFactory,
        ResourceModel\Watch $resourceModel,
        ResourceModel\Watch\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_watchFactory = $watchFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param WatchInterface $watch
     */
    public function save(WatchInterface $watch)
    {
        $watchId = $watch->getId();
        try {
            $this->_resourceModel->save($watch);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$watch->getId()]);
        return $this->getById($watch->getId());
    }

    /**
     * GetById function
     *
     * @param int $watchId
     */
    public function getById($watchId)
    {
        $watchData = $this->_watchFactory->create();
        $watchData->load($watchId);
        $this->_instancesById[$watchId] = $watchData;
        return $this->_instancesById[$watchId];
    }

    /**
     * GetList function
     *
     * @param SearchCriteriaInterface $searchCriteria
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
     * @param WatchInterface $watch
     */
    public function delete(WatchInterface $watch)
    {
        $watchId = $watch->getId();
        try {
            $this->_resourceModel->delete($watch);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove watch with id %1", $watchId)
            );
        }
        unset($this->_instancesById[$watchId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $watchId
     */
    public function deleteById($watchId)
    {
        $watch = $this->getById($watchId);
        return $this->delete($watch);
    }
}
