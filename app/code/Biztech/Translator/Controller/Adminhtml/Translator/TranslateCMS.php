<?php
/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */
namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Biztech\Translator\Model\Translator;
use Magento\Framework\Json\EncoderInterface;

class TranslateCMS extends Action
{
    /**
     * Translator Model
     * @var Biztech\Translator\Model\Translator
     */
    protected $translator;
    /**
     * @var Magento\Framework\Json\EncoderInterface
     */
    protected $encoderInterface;
    /**
     * @param Context          $context
     * @param EncoderInterface $encoderInterface
     * @param Translator       $translator
     */
    public function __construct(
        Context $context,
        EncoderInterface $encoderInterface,
        Translator $translator
    ) {
        $this->EncoderInterface = $encoderInterface;
        $this->translator = $translator;
        parent::__construct($context);
    }
    /**
     * Tranlsation of CMS page
     * @return JSON DATA.
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $data['value'] = str_replace("<span translate='no'>{{", "{{", $data['value']);
        $data['value'] = str_replace("}}</span>", "}}", $data['value']);
        $find_data = ['="{{', '}}"', '{{', '}}'];
        $replace_data = ['="((', '))"', '<span translate=\'no\'>{{', '}}</span>'];
        $newarr = ['="((', '))"'];
        $newarr1 = ['="{{', '}}"'];
        $data['value'] = str_replace($newarr, $newarr1, str_replace($find_data, $replace_data, $data['value']));
        $data['value'] = str_replace('(<span translate=\'no\'>{{', '({{', $data['value']);
        $data['value'] = str_replace('}}</span>)', '}})', $data['value']);
        $translate = [];
        $translate['id'] = $data['id'];
        $result = $this->translator->getTranslate($data['value'], $data['langto'], $data['langfrom']);
        $translate['value'] = $result;
        $translate['status'] = $result['status'];
        $this->getResponse()->setBody($this->EncoderInterface->encode($translate));
    }
}
