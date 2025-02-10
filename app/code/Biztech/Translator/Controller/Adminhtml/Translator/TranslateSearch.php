<?php

/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Biztech\Translator\Model\Search;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Result\PageFactory;

class TranslateSearch extends Action
{
    protected $_coreCache;
    protected $_resultPageFactory;
    protected $_searchModel;
    protected $encoderinterface;

    /**
     * @param Context                                  $context
     * @param CacheInterface                           $coreCache
     * @param PageFactory                              $resultPageFactory
     * @param \Magento\Framework\Json\EncoderInterface $encoderinterface
     * @param Search                                   $searchModel
     */
    public function __construct(
        Context $context,
        CacheInterface $coreCache,
        PageFactory $resultPageFactory,
        \Magento\Framework\Json\EncoderInterface $encoderinterface,
        Search $searchModel
    ) {
        $this->encoderinterface = $encoderinterface;
        $this->_coreCache = $coreCache;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_searchModel = $searchModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $cache = $this->_coreCache->load('translate_search_result');
        if ($cache) {
            $searchResult = json_decode($cache);
        } else {
            $searchResponse = [];
            $string = $this->getRequest()->getParam('searchString');
            $modules = $this->getRequest()->getParam('modules');
            $interface = $this->getRequest()->getParam('interface');
            $locale = $this->getRequest()->getParam('locale');
            $searchResult = $this->_searchModel->searchString($string, $locale, $modules, $interface);
            $this->_coreCache->save($string, 'translate_search_string', ['translate_cache'], null);
            $this->_coreCache->save(json_encode($string), 'translate_search_result', ['translate_cache'], null);
            $this->_coreCache->save('asc', 'translate_search_order', ['translate_cache'], null);
            $this->_coreCache->clean();
        }
        if (empty($searchResult)) {
            $searchResponse['data'] = __('No Data Found');
        } elseif (isset($searchResult['warning']) && $searchResult['warning'] == 'true') {
            $searchResponse['data'] = __('The search returned too many data. Please narrow your search');
        } elseif (isset($searchResult['msg']) && $searchResult['msg'] == 'true') {
            $searchResponse['data'] = __('No Data found');
        } else {
            $searchResponse['data'] = $resultPage->getLayout()->createBlock('\Biztech\Translator\Block\Adminhtml\Search\Grid')->setResults($searchResult)->setTemplate('Biztech_Translator::translator/search/grid.phtml')->toHtml();
        }
        $searchResponse = $this->encoderinterface->encode($searchResponse);
        $this->getResponse()->setBody($searchResponse);
    }
}
