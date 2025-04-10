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

use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class CarouselRepository model
 */
class CarouselRepository implements \Webkul\MobikulCore\Api\CarouselRepositoryInterface
{
    /**
     * ResourceModel Variable
     *
     * @var ResourceModel\Carousel
     */
    protected $_resourceModel;
    
    /**
     * Instances Variable
     *
     * @var Array
     */
    protected $_instances = [];
    
    /**
     * CarouselFactory Variable
     *
     * @var CarouselFactory
     */
    protected $_carouselFactory;
    
    /**
     * CollectionFactory Variable
     *
     * @var ResourceModel\Carousel\CollectionFactory
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
     * @param CarouselFactory $carouselFactory
     * @param ResourceModel\Carousel $resourceModel
     * @param ResourceModel\Carousel\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        CarouselFactory $carouselFactory,
        ResourceModel\Carousel $resourceModel,
        ResourceModel\Carousel\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_carouselFactory = $carouselFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param CarouselInterface $carousel
     */
    public function save(CarouselInterface $carousel)
    {
        $carouselId = $carousel->getId();
        try {
            $this->_resourceModel->save($carousel);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$carousel->getId()]);
        return $this->getById($carousel->getId());
    }

    /**
     * GetById function
     *
     * @param int $carouselId
     */
    public function getById($carouselId)
    {
        $carouselData = $this->_carouselFactory->create();
        $carouselData->load($carouselId);
        $this->_instancesById[$carouselId] = $carouselData;
        return $this->_instancesById[$carouselId];
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
     * @param CarouselInterface $carousel
     */
    public function delete(CarouselInterface $carousel)
    {
        $carouselId = $carousel->getId();
        try {
            $this->_resourceModel->delete($carousel);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove carousel with id %1", $carouselId)
            );
        }
        unset($this->_instancesById[$carouselId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $carouselId
     */
    public function deleteById($carouselId)
    {
        $carousel = $this->getById($carouselId);
        return $this->delete($carousel);
    }
}
