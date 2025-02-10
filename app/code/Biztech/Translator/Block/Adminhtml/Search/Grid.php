<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Search;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Element\Template;

class Grid extends Template
{

    protected $results;
    protected $cache;

    /**
     * Grid constructor.
     * @param Context $context
     * @param CacheInterface $coreCache
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->cache = $context->getCache();
        parent::__construct($context, $data);
    }

    /**
     * @param $result
     * @return $this
     */
    public function setResults($result)
    {
        $this->results = $result;

        return $this;
    }

    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param $extraParams
     * @return array
     */
    public function getRequestParams($extraParams)
    {
        $params = $this->getRequest()->getParams();

        foreach ($extraParams as $key => $extraParam) {
            $params[$key] = $extraParam;
        }

        unset($params['key']);
        unset($params['isAjax']);
        unset($params['form_key']);
        unset($params['searchString']);
        return $params;
    }

    /**
     * @return mixed|string
     */
    public function getSearchString()
    {
        $searchString = $this->cache->load('translate_search_result') ? $this->cache->load('translate_search_result') : $this->getRequest()->getParam('searchString');
        return $searchString;
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return preg_replace("~[^a-z0-9_]*~i", '', $this->getId()) . 'JsObject';
    }
}
