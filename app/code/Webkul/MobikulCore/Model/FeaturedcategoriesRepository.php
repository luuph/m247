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
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterface;

class FeaturedcategoriesRepository implements \Webkul\MobikulCore\Api\FeaturedcategoriesRepositoryInterface
{
    /**
     * ResourceModel Variable
     *
     * @var ResourceModel\Featuredcategories
     */
    protected $_resourceModel;
    
    /**
     * Instances Variable
     *
     * @var Array
     */
    protected $_instances = [];
    
    /**
     * CollectionFactory Variable
     *
     * @var ResourceModel\Featuredcategories\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * InstancesById Variable
     *
     * @var Array
     */
    protected $_instancesById = [];

    /**
     * FeaturedcategoriesFactory Variable
     *
     * @var FeaturedcategoriesFactory
     */
    protected $_featuredcategoriesFactory;

    /**
     * ExtensibleDataObjectConverter Variable
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Construct function
     *
     * @param ResourceModel\Featuredcategories $resourceModel
     * @param FeaturedcategoriesFactory $featuredcategoriesFactory
     * @param ResourceModel\Featuredcategories\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceModel\Featuredcategories $resourceModel,
        FeaturedcategoriesFactory $featuredcategoriesFactory,
        ResourceModel\Featuredcategories\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_featuredcategoriesFactory = $featuredcategoriesFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param FeaturedcategoriesInterface $featuredcategories
     */
    public function save(FeaturedcategoriesInterface $featuredcategories)
    {
        $featuredcategoriesId = $featuredcategories->getId();
        try {
            $this->_resourceModel->save($featuredcategories);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$featuredcategories->getId()]);
        return $this->getById($featuredcategories->getId());
    }

    /**
     * GetById function
     *
     * @param int $featuredcategoriesId
     */
    public function getById($featuredcategoriesId)
    {
        $featuredcategoriesData = $this->_featuredcategoriesFactory->create();
        $featuredcategoriesData->load($featuredcategoriesId);
        $this->_instancesById[$featuredcategoriesId] = $featuredcategoriesData;
        return $this->_instancesById[$featuredcategoriesId];
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
     * @param FeaturedcategoriesInterface $featuredcategories
     */
    public function delete(FeaturedcategoriesInterface $featuredcategories)
    {
        $featuredcategoriesId = $featuredcategories->getId();
        try {
            $this->_resourceModel->delete($featuredcategories);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove featuredcategories record with id %1", $featuredcategoriesId)
            );
        }
        unset($this->_instancesById[$featuredcategoriesId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $featuredcategoriesId
     */
    public function deleteById($featuredcategoriesId)
    {
        $featuredcategories = $this->getById($featuredcategoriesId);
        return $this->delete($featuredcategories);
    }
}
