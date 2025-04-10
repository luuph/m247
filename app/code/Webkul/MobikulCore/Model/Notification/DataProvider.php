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

namespace Webkul\MobikulCore\Model\Notification;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Notification;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Notification\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Session variable
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;
    
    /**
     * Collection variable
     *
     * @var Webkul\MobikulCore\Model\ResourceModel\Categoryimages\CollectionFactory
     */
    protected $collection;
    
    /**
     * LoadedData variable
     *
     * @var Mixed
     */
    protected $loadedData;

    /**
     * Construct function
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param NotificationCollectionFactory $notificationCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        NotificationCollectionFactory $notificationCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $notificationCollectionFactory->create();
        $this->collection->addFieldToSelect("*");
    }

    /**
     * GetSession function
     *
     * @return void
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        }
        return $this->session;
    }

    /**
     * GetData function
     *
     * @return void
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $notification) {
            $result["notification"] = $notification->getData();
            $this->loadedData[$notification->getId()] = $result;
        }
        $data = $this->getSession()->getNotificationFormData();
        if (!empty($data)) {
            $notificationId = isset($data["mobikul_notification"]["id"]) ? $data["mobikul_notification"]["id"] : null;
            $this->loadedData[$notificationId] = $data;
            $this->getSession()->unsNotificationFormData();
        }
        return $this->loadedData;
    }
}
