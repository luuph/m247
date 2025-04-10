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

namespace Webkul\MobikulCore\Model\Bannerimage;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Bannerimage;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Bannerimage\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Bannerimage\CollectionFactory as BannerimageCollectionFactory;

/**
 * Class DataProvider model
 */
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
     * LoadedData Variable
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
     * @param BannerimageCollectionFactory $bannerimageCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        BannerimageCollectionFactory $bannerimageCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $bannerimageCollectionFactory->create();
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
        foreach ($items as $bannerimage) {
            $result["bannerimage"] = $bannerimage->getData();
            $this->loadedData[$bannerimage->getId()] = $result;
        }
        $data = $this->getSession()->getBannerimageFormData();
        if (!empty($data)) {
            $bannerimageId = isset($data["mobikul_bannerimage"]["id"]) ? $data["mobikul_bannerimage"]["id"] : null;
            $this->loadedData[$bannerimageId] = $data;
            $this->getSession()->unsBannerimageFormData();
        }
        return $this->loadedData;
    }
}
