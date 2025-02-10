<?php

namespace Biztech\Translator\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\ObjectManagerInterface;

class EmailTemplateList implements ArrayInterface
{
    protected $_objectManager;
    public function __construct(ObjectManagerInterface $objectmanager)
    {
        $this->_objectManager = $objectmanager;
    }

    public function toOptionArray()
    {
        $alltemplele = $this->_objectManager->get('Magento\Email\Model\Template\Config');
        $alltemplele_data = $alltemplele->getAvailableTemplates();
        $templates = [
            ['value' => 'mass_product_translation_cron_success_template', 'label' => __('Mass Translation Cron Success Notification')],
        ];
        foreach ($alltemplele_data as $group => $options):
            $label = $options['label'];
            if (is_array($label)) {
                $templates[] = ['label' => $label->getText(), 'value' => $options['value']];
            } else {
                $templates[] = ['label' => $label, 'value' => $options['value']];
            }
        endforeach;
        return $templates;
    }
}
