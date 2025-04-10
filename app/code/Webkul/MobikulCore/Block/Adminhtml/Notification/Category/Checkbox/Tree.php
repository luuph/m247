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

namespace Webkul\MobikulCore\Block\Adminhtml\Notification\Category\Checkbox;

use Magento\Framework\Data\Tree\Node;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ResourceModel\Category\Tree as CategoryTree;
use Magento\Framework\Registry;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\DB\Helper;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Class Tree block
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * SelectedIds variable
     *
     * @var array
     */
    protected $_selectedIds  = [];
    
    /**
     * ExpandedPath variable
     *
     * @var array
     */
    protected $_expandedPath = [];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Webkul\MobikulCore\Api\NotificationRepositoryInterface
     */
    protected $notificationRepo;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @param Context $context
     * @param CategoryTree $categoryTree
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param EncoderInterface $jsonEncoder
     * @param Helper $resourceHelper
     * @param Session $backendSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepo
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context             $context,
        CategoryTree        $categoryTree,
        Registry            $registry,
        CategoryFactory     $categoryFactory,
        EncoderInterface    $jsonEncoder,
        Helper              $resourceHelper,
        Session             $backendSession,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepo,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        array               $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data,
            $secureRenderer
        );

        $this->request = $request;
        $this->notificationRepo = $notificationRepo;
        $this->serializer = $serializer;
    }

    /**
     * Function prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("notification/category/checkbox/tree.phtml");
    }

    /**
     * Function to get gategory Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $notificationId = $this->request->getParam("id");
        if ($notificationId) {
            $notification = $this->notificationRepo->getById($notificationId);
            $notificationData = $notification->getData();
            $filterData = $this->serializer->unserialize($notificationData["filter_data"]);
            $categoryIds = explode(",", $filterData["category_ids"]);
            $this->_selectedIds = $categoryIds;
        }
        return $this->_selectedIds;
    }

    /**
     * Function to get id string
     *
     * @return int
     */
    public function getIdsString()
    {
        return implode(",", $this->_selectedIds);
    }

    /**
     * Function to set category Ids
     *
     * @param mixed $ids
     * @return void
     */
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = [];
        } elseif (!is_array($ids)) {
            $ids = [(int)$ids];
        }
        $this->_selectedIds = $ids;
        return $this;
    }

    /**
     * Function to get expanded path
     *
     * @return string
     */
    protected function getExpandedPath()
    {
        return $this->_expandedPath;
    }

    /**
     * Function to set expanded path
     *
     * @param mixed $path
     * @return void
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode("/", $path));
        return $this;
    }

    /**
     * Function to get node jSon
     *
     * @param Node $node
     * @param integer $level
     * @return void
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
        $item["text"] = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $item["text"] .= " (" . $node->getProductCount() . ")";
        }
        $item["id"] = $node->getId();
        $item["path"] = $node->getData("path");
        $item["cls"] = "folder " . ($node->getIsActive() ? "active-category" : "no-active-category");
        $item["allowDrop"] = false;
        $item["allowDrag"] = false;
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData("path"));
            $item["checked"] = true;
        }
        if ($node->getLevel() < 2) {
            $this->setExpandedPath($node->getData("path"));
        }
        if ($node->hasChildren()) {
            $item["children"] = [];
            foreach ($node->getChildren() as $child) {
                $item["children"][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($item["children"]) && (int)$node->getChildrenCount() > 0) {
            $item["children"] = [];
        }
        $item["expanded"] = in_array($node->getId(), $this->getExpandedPath());
        return $item;
    }
}
