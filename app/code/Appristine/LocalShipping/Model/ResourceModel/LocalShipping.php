<?php

namespace Appristine\LocalShipping\Model\ResourceModel;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

class LocalShipping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public $_coreConfig;
    public $_storeManager;
    public $_countryCollectionFactory;
    public $_regionCollectionFactory;
    public $_filesystem;
    
    public function _construct()
    {
        $this->_init('ced_localshipping', 'id');
    }

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        $connectionName = null
    ) {
       
        $this->_coreConfig = $coreConfig;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context, $connectionName);
    }

    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        if (empty($_FILES['groups']['tmp_name']['localshipping']['fields']['import']['value'])) {
            return $this;
        }
        $connection=$this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable());
        $connection->commit();
        $csvFile = $_FILES['groups']['tmp_name']['localshipping']['fields']['import']['value'];
        $this->_importErrors = [];
        $this->_importedRows = 0;
        
        $tmpDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);

        $path = $tmpDirectory->getRelativePath($csvFile);
        
        $stream = $tmpDirectory->openFile($path);
        
        // check and skip headers
        $headers = $stream->readCsv();

        if ($headers === false || count($headers) < 2) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct File Format.'));
        }

        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = [];

            while (false !== ($csvLine = $stream->readCsv())) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = [];
                }
            }
            $this->_saveImportData($importData);
            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            print_r($e->getMessage());die;
            $connection->rollback();
            $stream->close();
            $this->_logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing zipcode and city.')
            );
        }

        $connection->commit();

        if ($this->_importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->_importErrors)
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }

        return $this;
    }
  
    
    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param  array $row
     * @param  int   $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 2) {
            $this->_importErrors[] = __('Please correct format in the Row #%1.', $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        $city = $row[0];
        $zipcode = $row[1];
        return [$city,$zipcode];
    }
    
    /**
     * Save import data batch
  	 *
     * @param  array $data
     * @return \Ced\AdvFlatRate\Model\Resource\Carrier\Advancedrate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = [
                'city','zipcode'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

  
}
