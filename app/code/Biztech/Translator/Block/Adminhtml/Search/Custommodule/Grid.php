<?php
/**
 * Copyright © 2021 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Block\Adminhtml\Search\Custommodule;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Grid extends Template
{
    protected $results;
    /**
     * Grid constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
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
        return $params;
    }
    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return preg_replace("~[^a-z0-9_]*~i", '', $this->getId()) . 'JsObject';
    }
}
