<?php

namespace Biztech\Translator\Model\Config\Source;

class Cronmailfor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
         $options = [
            ['value' => "translateinsinglestore", 'label' => __('Bulk Product Translation')],
            ['value' => "translateinmultiplestore", 'label' => __('Bulk Product Translation in Multiple store')],
            ['value' => "translatenewlyadded", 'label' => __('Newly Added Product Translation in Multiple store')],
         ];
         return $options;
    }
}
