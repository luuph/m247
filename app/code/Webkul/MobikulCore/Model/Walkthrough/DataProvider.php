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

namespace Webkul\MobikulCore\Model\Walkthrough;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Walkthrough;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Walkthrough\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Walkthrough\CollectionFactory as WalkthroughCollectionFactory;

/**
 * Class DataProvider model
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Session Variable
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;
    
    /**
     * Collection Variable
     *
     * @var WalkthroughCollectionFactory
     */
    protected $collection;
    
    /**
     * LoadedData Variable
     *
     * @var Mixed
     */
    protected $loadedData;

    /**
     * Undocumented function
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param WalkthroughCollectionFactory $walkthroughCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        WalkthroughCollectionFactory $walkthroughCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $walkthroughCollectionFactory->create();
        $this->collection->addFieldToSelect("*");
    }

    /**
     * GetSession function
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
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $walkthrough) {
            $result["walkthrough"] = $walkthrough->getData();
            $this->loadedData[$walkthrough->getId()] = $result;
        }
        $data = $this->getSession()->getWalkthroughFormData();
        if (!empty($data)) {
            $walkthroughId = $data["mobikul_walkthrough"]["id"] ?? null;
            $this->loadedData[$walkthroughId] = $data;
            $this->getSession()->unsWalkthroughFormData();
        }
        return $this->loadedData;
    }
}
