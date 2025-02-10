<?php
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Biztech\Translator\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;

class massTranslateCMSBlock extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $scopeConfig;
    protected $_logger;
    protected $_blockFactory;
    protected $_languageHelper;
    protected $_translatorHelper;
    protected $_translatorModel;
    protected $_bizhelper;
    protected $_storeManager;
    protected $productMetadataInterface;
    /**
     * @param Context                                  $context
     * @param Filter                                   $filter
     * @param CollectionFactory                        $collectionFactory
     * @param ScopeConfigInterface                     $scopeConfig
     * @param \Magento\Cms\Model\BlockFactory          $blockfactory
     * @param \Biztech\Translator\Helper\Language      $languageHelper
     * @param \Biztech\Translator\Helper\Translator    $translatorHelper
     * @param \Biztech\Translator\Model\Translator     $translatorModel
     * @param \Biztech\Translator\Helper\Logger\Logger $logger
     * @param Data                                     $bizhelper
     * @param StoreManagerInterface                    $storeManager
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, ScopeConfigInterface $scopeConfig, \Magento\Cms\Model\BlockFactory $blockfactory, \Biztech\Translator\Helper\Language $languageHelper, \Biztech\Translator\Helper\Translator $translatorHelper, \Biztech\Translator\Model\Translator $translatorModel, \Biztech\Translator\Helper\Logger\Logger $logger, Data $bizhelper, StoreManagerInterface $storeManager, ProductMetadataInterface $productMetadataInterface)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_blockFactory = $blockfactory;
        $this->_languageHelper = $languageHelper;
        $this->_translatorHelper = $translatorHelper;
        $this->_translatorModel = $translatorModel;
        $this->_logger = $logger;
        $this->_bizhelper = $bizhelper;
        $this->_storeManager = $storeManager;
        $this->productMetadataInterface = $productMetadataInterface;
        parent::__construct($context);
    }

    /**
     * For mass translating CMS page.
     * @return json response
     */
    public function execute()
    {
        $resultRedirect = $this
            ->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
        $params = $this->getRequest()
            ->getParams();
        if (array_key_exists('selected', $params))
        {
            $selectIds = $this->getRequest()
                ->getParam('selected');
            $implodeIds = implode(',', $selectIds);
            $cmsBlock = $this
                ->_blockFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('block_id', ['in' => $implodeIds]);
        }
        else
        {
            //$cmsBlock = $this->_blockFactory->create()->getCollection();
            $cmsBlock = $this
                ->filter
                ->getCollection($this
                ->collectionFactory
                ->create());
        }

        $filters = $this->getRequest()
            ->getParam('filters');
        if (isset($filters['store_id']))
        {
            $storeId = $filters['store_id'];
        }
        else
        {
            $storeId = 0;
        }
        /**
         * 19-11-2019 |  - activation for specific storeview
         */
        $enable = $this
            ->_bizhelper
            ->enableSiteForStoreview($storeId);
        if (!$enable)
        {
            $this
                ->messageManager
                ->addError(__('Couldn\'t work for the storeview : <b>' . $this
                ->_storeManager
                ->getStore($storeId)->getName() . '</b> | Please enable Language Translator for this storeview from  <b> Stores → Configuration → AppJetty Extensions → AppJetty Language Translator → Translator Activation.</b>'));
            return $resultRedirect->setPath('cms/block/index', ['store' => $storeId]);
        }
        /* end*/

        $selectBlockCount = count($cmsBlock);

        $translatedBlockCount = 0;
        $languages = $this
            ->_languageHelper
            ->getLanguages();
        if ($this->getRequest()
            ->getParam('lang_to') != 'locale')
        {
            $langto = $this->getRequest()
                ->getParam('lang_to');
        }
        else
        {
            $langto = $this
                ->_translatorHelper
                ->getLanguage($storeId);
        }
        $langFrom = $this
            ->_translatorHelper
            ->getFromLanguage($storeId);

        try
        {
            $pageTranslateFields = ['content', 'title'];
            $finalFields = $pageTranslateFields;
            foreach ($cmsBlock as $item)
            {
                foreach ($finalFields as $attributeCode)
                {
                    if (!isset($item->getData() [$attributeCode]) || empty($item->getData() [$attributeCode]))
                    {
                        continue;
                    }
                    $contentdata = $item->getData() [$attributeCode];
                    /*$version = $this->productMetadataInterface->getVersion();
                    if (version_compare($version, '2.1', '<')) {
                        if ($attributeCode == "page_content") {
                            $contentdata = str_replace('<span translate=\'no\'>{{', '{{', $contentdata);
                            $contentdata = str_replace('}}</span>', '}}', $contentdata);
                            $find_data = ['="{{', '}}"', '{{', '}}'];
                            $replace_data = ['="((', '))"', '<span translate=\'no\'>{{', '}}</span>'];
                            $newarr = ['="((', '))"'];
                            $newarr1 = ['="{{', '}}"'];
                            $contentdata = str_replace($newarr, $newarr1, str_replace($find_data, $replace_data, $contentdata));
                            $contentdata = str_replace('(<span translate=\'no\'>{{', '({{', $contentdata);
                            $contentdata = str_replace('}}</span>)', '}})', $contentdata);
                        }
                    } else {
                        if ($attributeCode == "content") {
                            $contentdata = str_replace('<span translate=\'no\'>{{', '{{', $contentdata);
                            $contentdata = str_replace('}}</span>', '}}', $contentdata);
                            $find_data = ['="{{', '}}"', '{{', '}}'];
                            $replace_data = ['="((', '))"', '<span translate=\'no\'>{{', '}}</span>'];
                            $newarr = ['="((', '))"'];
                            $newarr1 = ['="{{', '}}"'];
                            $contentdata = str_replace($newarr, $newarr1, str_replace($find_data, $replace_data, $contentdata));
                            $contentdata = str_replace('(<span translate=\'no\'>{{', '({{', $contentdata);
                            $contentdata = str_replace('}}</span>)', '}})', $contentdata);
                        }
                    }*/

                    /* compitible with page builder start here */
                    if ($attributeCode == "content" || $attributeCode == "page_content")
                    {
                        $m = array();
                        $n = array();

                        $skip_counce = preg_match_all('/{{([^}}]+)}}/', $contentdata, $n);
                        if ($skip_counce > 0)
                        {
                            foreach ($n[0] as $Skip_data => $value)
                            {
                                if (!str_contains($value, '{{store'))
                                {
                                    $contentdata = str_replace($value, '<span translate=\'no\'>' . "$value" . '</span>', $contentdata);
                                }
                            }
                        }

                        $contentdata = str_replace('&lt;', '<span translate=\'no\'>&lt;', $contentdata);
                        $contentdata = str_replace('&gt;', '&gt;</span>', $contentdata);

                    }
                    /* compitible with page builder end here */
                    $translate = $this
                        ->_translatorModel
                        ->getTranslate($contentdata, $langto, $langFrom);
                    /* compitible with page builder start here */
                    if ($attributeCode == "content" || $attributeCode == "page_content")
                    {
                        $m = array();
                        $n = array();
                        $skip_counce = preg_match_all('/{{([^}}]+)}}/', $translate['text'], $n);

                        if ($skip_counce > 0)
                        {
                            foreach ($n[0] as $Skip_data => $value)
                            {
                                $translateHtml = \html_entity_decode($value);
                                $translate["text"] = str_replace($value, $translateHtml, $translate['text']);
                            }
                        }
                        $translate['text'] = str_replace('<span translate=\'no\'>', '', $translate['text']);
                        $translate['text'] = str_replace('</span>', '', $translate['text']);
                        $translate['text'] = str_replace(';/ ', ';/', $translate['text']);
                        $translate['text'] = str_replace(' &gt', '&gt', $translate['text']);
                        $translate['text'] = str_replace('lt; ', 'lt;', $translate['text']);
                        $translate['text'] = str_replace('&lt; &lt', '&lt;', $translate['text']);
                    }
                    else
                    {
                        $translate["text"] = \html_entity_decode($translate["text"]);
                    }

                    /* compitible with page builder end here */

                    if (isset($translate['status']) && $translate['status'] == 'fail')
                    {
                        $error = '"' . $item->getData() ['block_id'] . '" can\'t be translate for "CMS Block : ' . $attributeCode . '" . Error : ' . $translate['text'];
                        $this
                            ->_logger
                            ->error($error);
                        $this
                            ->messageManager
                            ->addError($error);
                    }
                    else
                    {
                        $translate['text'] = str_replace('<span translate=\'no\'>{{', '{{', $translate['text']);
                        $translate['text'] = str_replace('}}</span>', '}}', $translate['text']);
                        $item->setData($attributeCode, $translate['text']);
                    }
                }
                try
                {
                    $item->save();
                    if (isset($translate['status']) && $translate['status'] != 'fail')
                    {
                        $translatedBlockCount++;
                    }
                }
                catch(LocalizedException $e)
                {
                    $this
                        ->_logger
                        ->debug($e->getRawMessage());
                }
            }
            if ($translatedBlockCount == 0)
            {
                $this
                    ->messageManager
                    ->addError(__('Any of CMS Block has not been translated. Please see /var/log/translator.log file for detailed information.'));
                $resultRedirect->setPath('cms/block/index');
                return $resultRedirect;
            }
            else
            {
                $this
                    ->messageManager
                    ->addSuccess($translatedBlockCount . __(' CMS Block(s) of ') . $selectBlockCount . __('has been translated to :') . $languages[$langto]);
            }
        }
        catch(LocalizedException $e)
        {
            $this
                ->_logger
                ->error($e->getRawMessage());
            $this
                ->messageManager
                ->addError($e->getRawMessage());
            $resultRedirect->setPath('cms/block/index');
        }
        $resultRedirect->setPath('cms/block/index');
        return $resultRedirect;
    }
}

