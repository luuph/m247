<?php
namespace Biztech\Translator\Helper\NewAddedProductTranslate;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/newAddedProductTranslate.log';
}
