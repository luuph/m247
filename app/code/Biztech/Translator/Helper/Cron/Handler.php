<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Helper\Cron;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/translator-cron.log';
}
