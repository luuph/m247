<?php

namespace Biztech\Translator\Block\Adminhtml\Cronlog;

class Logview extends \Magento\Backend\Block\Widget\Container
{
    public $_template = 'cron/logview.phtml';
    public $directoryList;
    public $fileDriver;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        array $data = []
    ) {
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        parent::__construct($context, $data);
    }

    /**
     * Get log file content
     *
     * @return string
     */
    public function getLogFileContent()
    {
        $filename = $this->getRequest()->getParam('cron').".log";
        $pathLogfile = $this->directoryList->getPath('log') . DIRECTORY_SEPARATOR . $filename;

        if ($this->fileDriver->isExists($pathLogfile)) {
            try {
                $contents = '';
                $handle = fopen($pathLogfile, 'r');
                if (!$handle) {
                    return "Log file is not readable or does not exist at this moment. File path is "
                    . $pathLogfile;
                }
                if (filesize($pathLogfile) > 0) {
                    $logsize = 500;
                    $file = file($pathLogfile);
                    for ($i = max(0, count($file)-$logsize); $i < count($file); $i++) {
                        $contents.= $file[$i];
                    }
                    if ($contents === false) {
                        return "Log file is not readable or does not exist at this moment. File path is "
                        . $pathLogfile;
                    }
                    fclose($handle);
                }
                return nl2br($contents);
            } catch (\Exception $e) {
                return $e->getMessage() . $pathLogfile;
            }
        } else {
            return "Cron Item(s) Log does not exist";
        }
    }
    public function getClearLogUrl()
    {
        return $this->getUrl(
            'translator/cron/logdelete',
            ['cron' => $this->getRequest()->getParam('cron')]
        );
    }
}
