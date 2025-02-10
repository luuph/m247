<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class validate
 *
 * Bss\RewardPoint\Controller\Adminhtml\Transaction
 */
class Validate extends Action
{
    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Import
     */
    protected $importModel;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * Validate constructor.
     * @param Action\Context $context
     * @param \Bss\RewardPoint\Model\ResourceModel\Import $importModel
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Bss\RewardPoint\Model\ResourceModel\Transaction $transactionResource
     * @param \Magento\Framework\File\Size $fileSize
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Bss\RewardPoint\Model\ResourceModel\Import $importModel,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Bss\RewardPoint\Model\ResourceModel\Transaction $transactionResource,
        \Magento\Framework\File\Size $fileSize,
        JsonFactory $resultJsonFactory
    ) {
        $this->importModel = $importModel;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->transactionResource = $transactionResource;
        $this->fileSize = $fileSize;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $file = $this->getRequest()->getFiles('file');
        $this->varDirectory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $filepath = "import/transactions/" . $file['name'];
        $size = $file['size'];
        $resultJson = $this->resultJsonFactory->create();
        $msesages = [];

        if (($size==0) || ($size > $this->fileSize->getMaxFileSize())) {
            $msesages['error'] = [$this->getMaxUploadSizeMessage()];
            $resultJson->setData($msesages);
            return $resultJson;
        }

        try {
            $target = $this->varDirectory->getAbsolutePath('import/transactions');
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(false);
            $result = $uploader->save($target);
            $this->importModel->setFilePath($filepath);
            $this->importModel->validateCsvFile();
            $errorMessages = [];
            $errorMessages[] = "Invalid Row(s): " . $this->transactionResource->getErrorRows();
            foreach ($this->getErrorMessages() as $code => $message) {
                $rowNums = $this->transactionResource->getErrorRows($code);
                if (!empty($rowNums)) {
                    $errorMessages[] = $message . $rowNums;
                }
            }
            if ($this->transactionResource->getInvalidRows() > 0) {
                $msesages['error'] = $errorMessages;
            } else {
                if ($result['file']) {
                    $msesages = [
                        'success' => [__("File is valid. Please click import button"),
                            __('File has been successfully uploaded in var/import/transactions')]
                    ];
                }
            }
        } catch (\Exception $e) {
            $msesages['error'] = [$e->getMessage()];
        }
        $resultJson->setData($msesages);
        return $resultJson;
    }

    /**
     * Get mas upload size message
     *
     * @return string
     */
    protected function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->fileSize->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1M.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }
        return $message;
    }

    /**
     * Get error message
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        return [
            "wrongWebsiteCode" => "Wrong website code in row(s): ",
            "customerNotExist" => "Customer Id not exist in row(s): ",
            "invalidDateRows" => "Wrong date format in row(s)"
        ];
    }

    /**
     * Is allow
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::transaction');
    }
}
