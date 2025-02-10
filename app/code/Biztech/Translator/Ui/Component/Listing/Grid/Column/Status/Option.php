<?php
/**
 * Copyright © 2020 Biztech . All rights reserved.
 **/

namespace Biztech\Translator\Ui\Component\Listing\Grid\Column\Status;

class Option implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options['success'] = ['value' => 'success', 'label' => __("Success")];
        $options['pending'] = ['value' => 'pending', 'label' => __("Pending")];
        $options['abort'] = ['value' => 'abort', 'label' => __("Aborted By Administrator")];
        $options['abort1'] = ['value' => 'abort1', 'label' => __("Aborted During Cron Process")];
        return $options;
    }
}
