<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Translator\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\DB\Helper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Biztech\Translator\Helper\Language;

class Categories extends \Magento\Catalog\Block\Adminhtml\Category\Tree implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    protected $language;

    protected $_template = 'Biztech_Translator::translator/catalog/category/tree.phtml';

    /**
     * @param Context          $context
     * @param Tree             $categoryTree
     * @param Registry         $registry
     * @param CategoryFactory  $categoryFactory
     * @param Store            $systemStore
     * @param EncoderInterface $jsonEncoder
     * @param Helper           $resourceHelper
     * @param Session          $backendSession
     * @param Language         $language
     * @param array            $data
     */
    public function __construct(
        Context $context,
        Tree $categoryTree,
        Registry $registry,
        CategoryFactory $categoryFactory,
        Store $systemStore,
        EncoderInterface $jsonEncoder,
        Helper $resourceHelper,
        Session $backendSession,
        Language $language,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->language = $language;
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        );
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Categories');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Categories');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @param \Magento\Framework\DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $categories = $this->getCategories();
        $arrCategory = explode(',', (string)$categories);
        $checked = '';
        if (in_array($node->getId(), $arrCategory)) {
            $checked .= 'checked="checked"';
        }
        $result = $this->escapeHtml($node->getName());
        return $result;
    }

    /**
     * @return mixed
     */
    public function getLanguages()
    {
        return $this->language->getLanguages();
    }

    public function getStoresSwitcherCustom()
    {
        $switcher =  $this->getLayout()->createBlock("Magento\Backend\Block\Store\Switcher")->setTemplate("Biztech_Translator::translator/catalog/category/storeswitcher/switcher.phtml")->toHtml();
        return $switcher;
    }

    /**
     * @param bool|null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null, $storeid = 0)
    {
        $params = ['_current' => true, 'id' => null, 'store' => $storeid];
        if ($expanded === null && $this->_backendSession->getIsTreeWasExpanded() || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('catalog/category/categoriesJson', $params);
    }

    /**
     * @param array|\Magento\Framework\Data\Tree\Node $node
     * @return bool
     */
    protected function _isCategoryMoveable($node)
    {
        return false;
    }
}
