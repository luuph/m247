<?php

namespace Biztech\Translator\Block\Adminhtml\Crondata;

use Biztech\Translator\Helper\Data as BizHelper;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;
    protected $helper;
    protected $status;
    protected $_fromLanguage;

    /**
     * @param \Magento\Backend\Block\Template\Context                     $context
     * @param \Magento\Backend\Helper\Data                                $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory                         $websiteFactory
     * @param \Biztech\Translator\Model\ResourceModel\Crondata\Collection $collectionFactory
     * @param \Magento\Framework\Module\Manager                           $moduleManager
     * @param \Biztech\Translator\Model\Config\Source\Status              $status
     * @param BizHelper                                                   $helper
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Biztech\Translator\Model\ResourceModel\Crondata\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Biztech\Translator\Model\Config\Source\Status $status,
        \Biztech\Translator\Model\Config\Source\Fromlanguage $fomLanguage,
        BizHelper $helper,
        array $data = []
    ) {
        $this->status = $status;
        $this->_logger = $context->getLogger();
        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->_fromLanguage = $fomLanguage;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('translator/cron/index', ['_current' => true]);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        if ($this->helper->isTranslatorEnabled()) {
            parent::_construct();
        }

        $this->setId('crondataGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            $collection = $this->_collectionFactory->load();
            $this->setCollection($collection);
            parent::_prepareCollection();
            return $this;
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'is_console',
            [
                'header' => __('Translate using'),
                'index' => 'is_console',
                'class' => 'is_console',
                'type' => 'options',
                'options' => $this->translateusing(),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\TranslationUsing'
            ]
        );
        $this->addColumn(
            'lang_from',
            [
                'header' => __('Language From'),
                'index' => 'lang_from',
                'class' => 'lang_from',
                'type' => 'options',
                'options' => $this->translateLanguage(),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\Langfrom'
            ]
        );

        $this->addColumn(
            'lang_to',
            [
                'header' => __('Language To'),
                'index' => 'lang_to',
                'class' => 'lang_to',
                'type' => 'options',
                'options' => $this->translateLanguage(),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\Langto'
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'header' => __('Store'),
                'index' => 'store_id',
                'class' => 'store_id',
                'type' => 'options',
                'options' => $this->storeName(),
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\Storename'
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Cron Status'),
                'index' => 'status',
                'class' => 'status',
                'type' => 'options',
                'options' => $this->status->toOptionArray()
            ]
        );

        $this->addColumn(
            'cron_product',
            [
                'header' => __('View Cron Product'),
                'index' => 'cron_product',
                'class' => 'cron_product',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Biztech\Translator\Block\Adminhtml\Crondata\Renderer\CronProduct'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
    public function translateusing()
    {
         return ["" => __("All") , "1" => __('Console') , "0" => __('Cron') ];
    }

    public function translateLanguage()
    {
        $languages = $this->_fromLanguage->toOptionArray();
        $_languages= [];
        $_languages[' '] = 'All';
        foreach ($languages as $language) {
            if ($language['value']=='auto') {
                $_languages[null] = $language['label'];
            } else {
                $_languages[$language['value']] = __(explode(":", $language['label'])[1]);
            }
        }
        return $_languages;
    }
    public function storeName()
    {
        $_storeName=[];
        $_storeName[' '] = 'All';
        $_storeName[0] = 'All Storeview';
        foreach ($this->_storeManager->getStores() as $key => $value) {
            $_storeName[$value->getStoreId()] = $value->getName();
        }
        return $_storeName;
    }
}
