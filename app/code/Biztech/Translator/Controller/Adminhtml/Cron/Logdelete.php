<?php

namespace Biztech\Translator\Controller\Adminhtml\Cron;

class Logdelete extends \Magento\Backend\App\Action
{
    protected $directoryList;
    protected $fileDriver;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
    }

    public function execute()
    {
        $filename = $this->getRequest()->getParam('cron').".log";
        $pathLogfile = $this->directoryList->getPath('log') . DIRECTORY_SEPARATOR . $filename;

        try {
            if ($this->fileDriver->isExists($pathLogfile)) {
                $this->fileDriver->deleteFile($pathLogfile);
                $this->messageManager->addSuccess(__('Log successfully deleted.'));
            } else {
                $this->messageManager->addError(__('We can\'t find first Cron item(s) log.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t find first Cron item(s) log.'));
        }
        return $this->_redirect('translator/cron/logview', ['cron' => $this->getRequest()->getParam('cron')]);
    }
}
