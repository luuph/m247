<?php

/**
 * Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved..
 */

namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action\Context;
use Biztech\Translator\Model\Translator;
use Magento\Catalog\Model\Product\Url;
use Magento\Backend\App\Action;

class Translate extends Action
{
    protected $translatorModel;
    protected $urlformat;
    protected $encoderInterface;

    /**
     * @param Context                                  $context
     * @param Url                                      $urlformat
     * @param Translator                               $translatorModel
     * @param \Magento\Framework\Json\EncoderInterface $encoderInterface
     */
    public function __construct(
        Context $context,
        Url $urlformat,
        Translator $translatorModel,
        \Magento\Framework\Json\EncoderInterface $encoderInterface
    ) {
        $this->urlformat = $urlformat;
        $this->translatorModel = $translatorModel;
        $this->encoderInterface = $encoderInterface;
        parent::__construct($context);
    }

    /**
     * @return Json Response
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data['value'] != '') {
            $result = $this->translatorModel->getTranslate($data['value'], $data['langto'], $data['langfrom']);

            if ($data['id'] == '#url_key') {
                $r = [];

                foreach ($result as $key => $value) {
                    $r[$key] = $value;
                    if ($key == 'text') {
                        $urlKey = $this->urlformat->formatUrlKey($value);
                        if ($urlKey == '') {
                            $r[$key] = $data['value'];
                        } else {
                            $r[$key] = $urlKey;
                        }
                    }
                }
                $translate = [
                    'id' => $data['id'],
                    'value' => $r,
                    'status' => $r['status']
                ];
            } else {
                $translate = [
                    'id' => $data['id'],
                    'value' => $result,
                    'status' => $result['status']
                ];
            }
        } else {
            $result = [
                'text' => 'There is no data to translate.',
                'status' => 'fail'
            ];
            $translate = [
                'id' => $data['id'],
                'value' => $result,
                'status' => $result['status']
            ];
        }
        $this->getResponse()->setBody($this->encoderInterface->encode($translate));
    }
}
