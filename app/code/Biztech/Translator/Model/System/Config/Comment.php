<?php

namespace Biztech\Translator\Model\System\Config;

use \Magento\Config\Model\Config\CommentInterface;
use \Biztech\Translator\Helper\Data;

class Comment implements CommentInterface
{

    protected $_helper;

    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function getCommentText($elementValue)
    {
        $html = __('To get the activation key, you can contact us at <a href="https://www.appjetty.com/support.htm" target="-">appjetty</a>');
        $notifyfromdate = $this->_helper->moduleInstallDate();
        $notify_time = date('Y-m-d h:i:s', strtotime($notifyfromdate . ' + 2 day'));
        $current_time = date('Y-m-d H:i:s');
        if ($notify_time>$current_time) {
            $html .= "<p class='message message-warning db-backup-message'>".__("Please make sure to have backup of your database before proceeding with translation.")."</p>";
        }
        return $html;
    }
}
