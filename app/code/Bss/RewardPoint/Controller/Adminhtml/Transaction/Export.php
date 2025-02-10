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
 * @copyright  Copyright (c) 2019-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Controller\Adminhtml\Transaction;

/**
 * Class export
 *
 * Bss\RewardPoint\Controller\Adminhtml\Transaction
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Export extends \Magento\Backend\App\Action
{

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Export constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                $context
     * @param \Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem                                      $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                        $datetime
     * @param \Magento\Framework\Filesystem\Io\File                              $io
     * @param \Magento\Framework\File\Csv                                        $csv
     * @param \Magento\Framework\App\Response\Http\FileFactory                   $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory                    $resultRawFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface               $timezone
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filesystem        = $filesystem;
        $this->datetime          = $datetime;
        $this->io                = $io;
        $this->csv               = $csv;
        $this->fileFactory       = $fileFactory;
        $this->resultRawFactory  = $resultRawFactory;
        $this->timezone          = $timezone;
    }


    /**
     * Export reward point
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $data         = $this->collectionFactory->create()->getExportData();
        $varDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        $dir = $varDirectory->getAbsolutePath('export/transactions');
        $this->io->mkdir($dir, 0775);

        if ($this->getRequest()->getParam('export_file_type') == 'CSV') {
            $currentDate = $this->formatDate($this->datetime->date());
            $outputFile  = $dir . '/Transaction_' . $currentDate . '.csv';
            $fileName    = 'Transaction_' . $currentDate . '.csv';
            try {
                $this->csv->saveData($outputFile, $data);
                return $this->fileFactory->create(
                    $fileName,
                    [
                        'type'  => 'filename',
                        'value' => 'export/transactions/' . $fileName,
                        'rm'    => true,
                    ],
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'text/csv',
                    null
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Format date
     *
     * @param  string $dateTime
     * @return string
     */
    protected function formatDate($dateTime)
    {
        $dateTimeAsTimeZone = $this->timezone->date($dateTime)->format('YmdHis');
        return $dateTimeAsTimeZone;
    }

    /**
     * Is allow
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::transaction');
    }
}
