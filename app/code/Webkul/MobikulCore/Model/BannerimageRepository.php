<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Webkul\MobikulCore\Api\Data\BannerimageInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class BannerimageRepository model
 */
class BannerimageRepository implements \Webkul\MobikulCore\Api\BannerimageRepositoryInterface
{
    /**
     * ResourceModel variable
     *
     * @var ResourceModel\Bannerimage
     */
    protected $_resourceModel;
    
    /**
     * Instances variable
     *
     * @var Array
     */
    protected $_instances = [];
    
    /**
     * CollectionFactory variable
     *
     * @var ResourceModel\Bannerimage\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * BannerimageFactory variable
     *
     * @var BannerimageFactory
     */
    protected $_bannerimageFactory;
    
    /**
     * InstancesById variable
     *
     * @var Array
     */
    protected $_instancesById = [];
    
    /**
     * ExtensibleDataObjectConverter variable
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Construct function
     *
     * @param BannerimageFactory $bannerimageFactory
     * @param ResourceModel\Bannerimage $resourceModel
     * @param ResourceModel\Bannerimage\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        BannerimageFactory $bannerimageFactory,
        ResourceModel\Bannerimage $resourceModel,
        ResourceModel\Bannerimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_bannerimageFactory = $bannerimageFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param BannerimageInterface $bannerimage
     * @return void
     */
    public function save(BannerimageInterface $bannerimage)
    {
        $bannerimageId = $bannerimage->getId();
        try {
            $this->_resourceModel->save($bannerimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$bannerimage->getId()]);
        return $this->getById($bannerimage->getId());
    }

    /**
     * GetById function
     *
     * @param int $bannerimageId
     * @return void
     */
    public function getById($bannerimageId)
    {
        $bannerimageData = $this->_bannerimageFactory->create();
        $bannerimageData->load($bannerimageId);
        $this->_instancesById[$bannerimageId] = $bannerimageData;
        return $this->_instancesById[$bannerimageId];
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
     * @param BannerimageInterface $bannerimage
     * @return void
     */
    public function delete(BannerimageInterface $bannerimage)
    {
        $bannerimageId = $bannerimage->getId();
        try {
            $this->_resourceModel->delete($bannerimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove banner image with id %1", $bannerimageId)
            );
        }
        unset($this->_instancesById[$bannerimageId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $bannerimageId
     * @return void
     */
    public function deleteById($bannerimageId)
    {
        $bannerimage = $this->getById($bannerimageId);
        return $this->delete($bannerimage);
    }
}
