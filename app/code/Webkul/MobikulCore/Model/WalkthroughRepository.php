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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\WalkthroughInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class WalkthroughRepository model
 */
class WalkthroughRepository implements \Webkul\MobikulCore\Api\WalkthroughRepositoryInterface
{
    /**
     * ResourceModel variable
     *
     * @var ResourceModel\Walkthrough
     */
    protected $_resourceModel;

    /**
     * Instances variable
     *
     * @var array
     */
    protected $_instances = [];

    /**
     * CollectionFactory variable
     *
     * @var ResourceModel\Walkthrough\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * InstancesById variable
     *
     * @var array
     */
    protected $_instancesById = [];
    
    /**
     * CarouselimageFactory variable
     *
     * @var array
     */
    protected $_carouselimageFactory;

    /**
     * ExtensibleDataObjectConverter variable
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * WalkthroughFactory variable
     *
     * @var WalkthroughFactory
     */
    protected $_walkthroughFactory;

    /**
     * Construct function
     *
     * @param WalkthroughFactory $walkthroughFactory
     * @param ResourceModel\Walkthrough $resourceModel
     * @param ResourceModel\Walkthrough\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        WalkthroughFactory $walkthroughFactory,
        ResourceModel\Walkthrough $resourceModel,
        ResourceModel\Walkthrough\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_walkthroughFactory = $walkthroughFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param WalkthroughInterface $walkthrough
     * @return void
     */
    public function save(WalkthroughInterface $walkthrough)
    {
        $walkthroughId = $walkthrough->getId();
        try {
            $this->_resourceModel->save($walkthrough);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$walkthrough->getId()]);
        return $this->getById($walkthrough->getId());
    }

    /**
     * GetById function
     *
     * @param int $walkthroughId
     * @return void
     */
    public function getById($walkthroughId)
    {
        $walkthroughData = $this->_walkthroughFactory->create();
        $walkthroughData->load($walkthroughId);
        $this->_instancesById[$walkthroughId] = $walkthroughData;
        return $this->_instancesById[$walkthroughId];
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
     * @param WalkthroughInterface $walkthrough
     * @return void
     */
    public function delete(WalkthroughInterface $walkthrough)
    {
        $walkthroughId = $walkthrough->getId();
        try {
            $this->_resourceModel->delete($walkthrough);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove walk through with id %1", $walkthroughId)
            );
        }
        unset($this->_instancesById[$walkthroughId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $walkthroughId
     * @return void
     */
    public function deleteById($walkthroughId)
    {
        $walkthrough = $this->getById($walkthroughId);
        return $this->delete($walkthrough);
    }
}
