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
namespace Bss\RewardPoint\Model\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;

class Import
{
    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    protected $sourceCsvFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var Transaction
     */
    protected $transactionResource;

    /**
     * Import constructor.
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Transaction $transactionResource
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\Framework\Filesystem $filesystem,
        Transaction $transactionResource
    ) {
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->filesystem = $filesystem;
        $this->transactionResource = $transactionResource;
    }

    /**
     * Import from csv file
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile()
    {
        $sourceCsv = $this->createSourceCsvModel($this->getFilePath());

        $sourceCsv->rewind();
        $numRow = 0;
        while ($sourceCsv->valid()) {
            $numRow++;
            $this->transactionResource->processData($sourceCsv->current(), $numRow);
            $sourceCsv->next();
        }
    }

    /**
     * Validate csv file
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function validateCsvFile()
    {
        $sourceCsv = $this->createSourceCsvModel($this->getFilePath());

        $sourceCsv->rewind();
        $numRow = 0;
        while ($sourceCsv->valid()) {
            $numRow++;
            $this->transactionResource->validateBeforeImport($sourceCsv->current(), $numRow);
            $sourceCsv->next();
        }
    }

    /**
     * Create source csv model
     *
     * @param string $sourceFile
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function createSourceCsvModel($sourceFile)
    {
        return $this->sourceCsvFactory->create(
            [
                'file' => $sourceFile,
                'directory' => $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ]
        );
    }

    /**
     * Set file path
     *
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
