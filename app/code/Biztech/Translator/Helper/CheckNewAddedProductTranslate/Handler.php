<?php
namespace Biztech\Translator\Helper\CheckNewAddedProductTranslate;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/checkNewAddedProductTranslate.log';
}
