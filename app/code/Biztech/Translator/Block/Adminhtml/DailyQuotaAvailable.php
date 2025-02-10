<?php

namespace Biztech\Translator\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Biztech\Translator\Model\Logcron;

class DailyQuotaAvailable extends Field
{
    protected $_helper;
    protected $_assetRepo;
    protected $_logCron;
    protected $_date;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Biztech\Translator\Helper\Data $helper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Stdlib\DateTime\DateTime $_dateTime,
        Logcron $logCron,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_assetRepo = $assetRepo;
        $this->_logCron = $logCron;
        $this->_date = $_dateTime;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $websites = $this->_helper->getAllWebsites();
        if (!empty($websites)) {
            $html = $element->getElementHtml();
            $_logCron = $this->_logCron->getCollection()->getLastItem();
            $_charCutLimit = (int)$this->_helper->getConfigValue('translator/general/google_daily_cut_before_limit');
            $characterLimit = (int)$this->_helper->getConfigValue('translator/general/google_daily_limit') - $_charCutLimit;
            /* Remaining limit */
            if ($_logCron->getRemainLimit() > 0 && $this->_date->gmtDate('d-m-Y') == date('d-m-Y', strtotime($_logCron->getCronDate()))) {
                $characterLimit = $_logCron->getRemainLimit();
            }
            if ($characterLimit!='' && $characterLimit!=null) {
                $html.="<p class='dailyquota-available'>Available Today's Daily Quota:<b>".$characterLimit."</b></p>";
            }
            return $html;
        }
    }
}
