<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/
namespace Biztech\Translator\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {

        $options = [
            'success' => __('Success'),
            'pending' => __('Pending'),
            'abort' => __('Aborted By Administrator'),
            'abort1' => __('Aborted During Cron Process')
        ];

        return $options;
    }
}
