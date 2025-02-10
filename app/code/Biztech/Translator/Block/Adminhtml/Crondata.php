<?php

namespace Biztech\Translator\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Biztech\Translator\Helper\Data;

class Crondata extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $helper;

    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        
        $this->_controller = 'adminhtml_crondata';/*block grid.php directory*/
        $this->_blockGroup = 'Biztech_Translator';
        $this->_headerText = __('Cron Translation Data');
        $this->buttonList->add(
            'cron_log_view',
            [
            'label' => __('View Cron Log'),
            'class' => 'action-scalable action-secondary',
            'onclick' => "setLocation('{$this->getUrl('translator/cron/logview/cron/translator-cron')}')"
            ]
        );
        if ($this->helper->isTranslatorEnabled()) {
            parent::_construct();
        }
        $this->removeButton('add');
    }
}
