<?php

/**
 * Copyright © 2021 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Module\Dir;
use Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory;
use Magento\Setup\Module\I18n\Parser\Parser;
use Biztech\Translator\Model\Translator;
use Biztech\Translator\Helper\Translator as LangHelper;
use Magento\Setup\Module\I18n\FilesCollector;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Translation\Model\ResourceModel\StringUtils;
use Magento\Framework\App\CacheInterface;

class StaticdataSerchandtranslate extends Action
{
    protected $_resultPageFactory;
    protected $_moduleDir;
    protected $encoderinterface;
    protected $translatorModel;
    protected $_translatorHelper;
    protected $scopeConfig;
    protected $stringUtils;
    protected $cacheManager;
    protected $optionResolverFactory;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    private static $_factory;

    /**
     * Context manager
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    private static $_context;

    /**
     * Dictionary generator
     *
     * @var \Magento\Setup\Module\I18n\Dictionary\Generator
     */
    private static $_dictionaryGenerator;
    /**
     * @param Context                                  $context
     * @param PageFactory                              $resultPageFactory
     * @param \Magento\Framework\Json\EncoderInterface $encoderinterface
     * @param Dir                                   $moduleDir
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Json\EncoderInterface $encoderinterface,
        Dir $moduleDir,
        ResolverFactory $optionsResolver,
        Translator $translatorModel,
        LangHelper $translatorHelper,
        ScopeConfigInterface $scopeConfig,
        StringUtils $stringUtils,
        CacheInterface $cacheManager
    ) {
        $this->encoderinterface = $encoderinterface;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_moduleDir = $moduleDir;
        $this->optionResolverFactory = $optionsResolver;
        $this->translatorModel = $translatorModel;
        $this->_translatorHelper = $translatorHelper;
        $this->scopeConfig = $scopeConfig;
        $this->stringUtils = $stringUtils;
        $this->cacheManager = $cacheManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $searchResponse = [];
        $searchResult = [];
        $directory = $this->_moduleDir->getDir($this->getRequest()->getParam('custom_Modules'));
        $store_id = $this->getRequest()->getParam('translate_in_store_id');
        if ($store_id!='' || $store_id!=null) {
            $lang_to = $this->getRequest()->getParam('translate_to_language');
            if ($lang_to == 'locale') {
                $lang_to = $this->_translatorHelper->getLanguage($store_id);
            }
            $locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
            $generator = self::getDictionaryGenerator();
            $optionResolver = $this->optionResolverFactory->create($directory, false);
            $parser = $generator->getDirectoryParser(false);
            $parser->parse($optionResolver->getOptions());
            $phraseList = $parser->getPhrases();
            $googleError = 'Something went wrong!';
            if (count($phraseList)) {
                $i=1;
                foreach ($phraseList as $originalString => $row_data) {
                    $searchResult[$i]['origional'] = $originalString;
                    if ($originalString != strip_tags($originalString)) {
                        $find_data = ['="{{', '}}"', '{{', '}}'];
                        $replace_data = ['="((', '))"', '<span class="notranslate">{{', '}}</span>'];
                        $newarr = ['="((', '))"'];
                        $newarr1 = ['="{{', '}}"'];
                        $originalString = str_replace($newarr, $newarr1, str_replace($find_data, $replace_data, $originalString));
                    }
                    $string = str_replace("'", "\\", $originalString);
                    $result = $this->translatorModel->getTranslate($string, $lang_to, '');
                    if ($result['status'] == 'success') {
                        $translateString = $result['text'];
                        $this->stringUtils->saveTranslate($originalString, $translateString, $locale, $store_id);
                        $searchResult[$i]['translated'] = $translateString;
                    } else {
                        $searchResult['error'] = true;
                        $googleError = $result['text'];
                        break;
                    }
                    $i++;
                }
            }
            $this->cacheManager->clean();
        }
        if (empty($searchResult)) {
            $searchResponse['data'] = __('No Data Found');
        } elseif (isset($searchResult['error']) && $searchResult['error'] == 'true') {
            $searchResponse['data'] = __($googleError);
        } else {
            $searchResponse['data'] = $resultPage->getLayout()->createBlock('\Biztech\Translator\Block\Adminhtml\Search\Custommodule\Grid')->setResults($searchResult)->setTemplate('Biztech_Translator::translator/search/custommodule/grid.phtml')->toHtml();
        }
        $searchResponse = $this->encoderinterface->encode($searchResponse);
        $this->getResponse()->setBody($searchResponse);
    }
    /**
     * Get dictionary generator
     *
     * @return \Magento\Setup\Module\I18n\Dictionary\Generator
     */
    public static function getDictionaryGenerator()
    {
        if (null === self::$_dictionaryGenerator) {
            $filesCollector = new FilesCollector();

            $phraseCollector = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer());
            $adapters = [
                'php' => new \Magento\Setup\Module\I18n\Parser\Adapter\Php($phraseCollector),
                'html' => new \Magento\Setup\Module\I18n\Parser\Adapter\Html(),
                'js' => new \Magento\Setup\Module\I18n\Parser\Adapter\Js(),
                'xml' => new \Magento\Setup\Module\I18n\Parser\Adapter\Xml(),
            ];

            $parser = new \Magento\Setup\Module\I18n\Parser\Parser($filesCollector, self::_getFactory());
            $parserContextual = new \Magento\Setup\Module\I18n\Parser\Contextual($filesCollector, self::_getFactory(), self::_getContext());
            foreach ($adapters as $type => $adapter) {
                $parser->addAdapter($type, $adapter);
                $parserContextual->addAdapter($type, $adapter);
            }

            self::$_dictionaryGenerator = new \Biztech\Translator\Module\I18n\Dictionary\Generator(
                $parser,
                $parserContextual,
                self::_getFactory(),
                new \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory()
            );
        }
        return self::$_dictionaryGenerator;
    }

    /**
     * Get factory
     *
     * @return \Magento\Setup\Module\I18n\Factory
     */
    private static function _getFactory()
    {
        if (null === self::$_factory) {
            self::$_factory = new \Magento\Setup\Module\I18n\Factory();
        }
        return self::$_factory;
    }

    /**
     * Get context
     *
     * @return \Magento\Setup\Module\I18n\Context
     */
    private static function _getContext()
    {
        if (null === self::$_context) {
            self::$_context = new \Magento\Setup\Module\I18n\Context(new ComponentRegistrar());
        }
        return self::$_context;
    }
}
