<?php
namespace Biztech\Translator\Helper\MassTranslateInAllStoreviewLogger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/masstranslateinallstorecron.log';
}
