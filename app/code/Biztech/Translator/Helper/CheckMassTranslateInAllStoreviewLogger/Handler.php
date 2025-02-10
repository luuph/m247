<?php
namespace Biztech\Translator\Helper\CheckMassTranslateInAllStoreviewLogger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/checkMassTranslateInAllStoreviewLogger.log';
}
