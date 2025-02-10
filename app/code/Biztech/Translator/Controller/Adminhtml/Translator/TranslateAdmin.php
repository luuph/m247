<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Biztech\Translator\Model\Translator;

class TranslateAdmin extends Action
{
    protected $translatorModel;

    /**
     * @param Context    $context
     * @param Translator $translatorModel
     */
    public function __construct(
        Context $context,
        Translator $translatorModel
    ) {
        $this->translatorModel = $translatorModel;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $string = $data['value'];
        if ($string != strip_tags($string)) {
            $find_data = ['="{{', '}}"', '{{', '}}'];
            $replace_data = ['="((', '))"', '<span class="notranslate">{{', '}}</span>'];
            $newarr = ['="((', '))"'];
            $newarr1 = ['="{{', '}}"'];
            $string = str_replace($newarr, $newarr1, str_replace($find_data, $replace_data, $string));
        }
        $string1 = str_replace("'", "\\", $string);
        $langto = explode('_', !$data['langto']==null ? $data['langto'] : '');
        $result = $this->translatorModel->getTranslate($string1, $langto[0], $data['langfrom']);
        $result1 = str_replace("\\", "'", $result);
        $result = $this->getResponse()->setBody(json_encode($result1));
        return $result;
    }
}
