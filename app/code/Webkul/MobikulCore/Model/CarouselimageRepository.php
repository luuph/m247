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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class CarouselimageRepository model
 */
class CarouselimageRepository implements \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface
{
    /**
     * ResourceModel Variable
     *
     * @var ResourceModel
     */
    protected $_resourceModel;

    /**
     * Instances Variable
     *
     * @var Instances
     */
    protected $_instances = [];

    /**
     * CollectionFactory Variable
     *
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * InstancesById Variable
     *
     * @var InstancesById
     */
    protected $_instancesById = [];

    /**
     * CarouselimageFactory Variable
     *
     * @var CarouselimageFactory
     */
    protected $_carouselimageFactory;

    /**
     * ExtensibleDataObjectConverter Variable
     *
     * @var ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;
    
    /**
     * Construct function
     *
     * @param CarouselimageFactory $carouselimageFactory
     * @param ResourceModel\Carouselimage $resourceModel
     * @param ResourceModel\Carouselimage\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        CarouselimageFactory $carouselimageFactory,
        ResourceModel\Carouselimage $resourceModel,
        ResourceModel\Carouselimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_carouselimageFactory = $carouselimageFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param CarouselimageInterface $carouselimage
     */
    public function save(CarouselimageInterface $carouselimage)
    {
        $carouselimageId = $carouselimage->getId();
        try {
            $this->_resourceModel->save($carouselimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$carouselimage->getId()]);
        return $this->getById($carouselimage->getId());
    }

    /**
     * GetById function
     *
     * @param int $carouselimageId
     */
    public function getById($carouselimageId)
    {
        $carouselimageData = $this->_carouselimageFactory->create();
        $carouselimageData->load($carouselimageId);
        $this->_instancesById[$carouselimageId] = $carouselimageData;
        return $this->_instancesById[$carouselimageId];
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
     * @param CarouselimageInterface $carouselimage
     */
    public function delete(CarouselimageInterface $carouselimage)
    {
        $carouselimageId = $carouselimage->getId();
        try {
            $this->_resourceModel->delete($carouselimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove carousel image with id %1", $carouselimageId)
            );
        }
        unset($this->_instancesById[$carouselimageId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $carouselimageId
     */
    public function deleteById($carouselimageId)
    {
        $carouselimage = $this->getById($carouselimageId);
        return $this->delete($carouselimage);
    }
}
