<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\Config\Source;

use Biztech\Translator\Helper\Data;

class Enabledisable implements \Magento\Framework\Option\ArrayInterface
{
    protected $helper;

    /**
     * @param Data $helperdata [description]
     */
    public function __construct(
        Data $helperdata
    ) {
        $this->helper = $helperdata;
    }

    public function toOptionArray()
    {
        $options = [
            ['value' => 0, 'label' => __('No')],
        ];
        $websites = $this->helper->getAllWebsites();
        if (!empty($websites)) {
            $options[] = ['value' => 1, 'label' => __('Yes')];
        }
        return $options;
    }
}
