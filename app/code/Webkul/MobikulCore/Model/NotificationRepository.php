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
use Webkul\MobikulCore\Api\Data\NotificationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class NotificationRepository implements \Webkul\MobikulCore\Api\NotificationRepositoryInterface
{
    /**
     * NotificationFactory variable
     *
     * @var NotificationFactory
     */
    protected $_notificationFactory;
    
    /**
     * Instances variable
     *
     * @var Array
     */
    protected $_instances = [];
    
    /**
     * InstancesById variable
     *
     * @var Array
     */
    protected $_instancesById = [];
    
    /**
     * CollectionFactory variable
     *
     * @var ResourceModel\Notification\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * ResourceModel variable
     *
     * @var ResourceModel\Notification
     */
    protected $_resourceModel;
    
    /**
     * ExtensibleDataObjectConverter variable
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Construct function
     *
     * @param NotificationFactory $notificationFactory
     * @param ResourceModel\Notification $resourceModel
     * @param ResourceModel\Notification\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        ResourceModel\Notification $resourceModel,
        ResourceModel\Notification\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_notificationFactory = $notificationFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save function
     *
     * @param NotificationInterface $notification
     * @return void
     */
    public function save(NotificationInterface $notification)
    {
        $notificationId = $notification->getId();
        try {
            $this->_resourceModel->save($notification);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$notification->getId()]);
        return $this->getById($notification->getId());
    }

    /**
     * GetById function
     *
     * @param int $notificationId
     * @return void
     */
    public function getById($notificationId)
    {
        $notificationData = $this->_notificationFactory->create();
        $notificationData->load($notificationId);
        $this->_instancesById[$notificationId] = $notificationData;
        return $this->_instancesById[$notificationId];
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
     * @param NotificationInterface $notification
     * @return void
     */
    public function delete(NotificationInterface $notification)
    {
        $notificationId = $notification->getId();
        try {
            $this->_resourceModel->delete($notification);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove notification record with id %1", $notificationId)
            );
        }
        unset($this->_instancesById[$notificationId]);
        return true;
    }

    /**
     * DeleteById function
     *
     * @param int $notificationId
     * @return void
     */
    public function deleteById($notificationId)
    {
        $notification = $this->getById($notificationId);
        return $this->delete($notification);
    }
}
