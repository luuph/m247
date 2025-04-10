<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Model\Product\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;

class IosFile extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Webkul\WebAr\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    
    /**
     * Initialize dependencies
     *
     * @param \Webkul\WebAr\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @return void
     */
    public function __construct(
        \Webkul\WebAr\Logger\Logger $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_file = $file;
        $this->resource = $resource;
        $this->jsonHelper = $jsonHelper;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * After product save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        $productSkuArr = [];
        $post = $this->request->getPostValue();
        $files =  $this->request->getFiles();
        $storeId = $this->request->getParam('store', 0);

        if (!empty($post['configurable-matrix-serialized'])) {
            $varArray =  $this->jsonHelper->jsonDecode($post['configurable-matrix-serialized'] ?? '{}');
            foreach ($varArray as $data) {
                array_push($productSkuArr, $data['sku']);
            }
            if (!empty($productSkuArr)) {
                if (in_array($object->getSku(), $productSkuArr)) {
                    return $this;
                }
            }
        }
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'catalog/product/glbmodels/ios/'
        );
        $delete = $object->getData($this->getAttribute()->getName() . '_delete');

        try {
            if ($delete) {
                $iosFileName = $object->getData($this->getAttribute()->getName());
                
                $connection = $this->resource->getConnection();

                //Get table name with prefix
                $tableName = $this->resource->getTableName(
                    'catalog_product_entity_varchar'
                );
              
                $select = $connection->select()
                    ->from(
                        ['c' => $tableName],
                        ['*']
                    )
                    ->where(
                        'c.entity_id = :entity_id'
                    )->where(
                        'c.attribute_id = :attribute_id'
                    );

                $attributeId = $this->getAttribute()->getId();
                
                $bind = [
                    'entity_id' => $object->getId(),
                    'attribute_id' => $attributeId
                ];
                $responseData = $connection->fetchAll($select, $bind);

                foreach ($responseData as $rowId => $rowData) {
                    if ($storeId == $rowData["store_id"]) {
                        if (($rowData["value"] ?? "") != $iosFileName) {
                            return $this;
                        }
                    }
                }
                
                //Delete Data from table
                $whereConditions = [$connection->quoteInto('value = ?', $iosFileName),];
                $connection->beginTransaction();
                try {
                    $connection->delete($tableName, $whereConditions);
                    $connection->commit();
                } catch (\Exception $e) {
                    $connection->rollBack();
                    throw $e;
                }
                ////////

                //Set Updated Value in Attribute//
                $object->setData($this->getAttribute()->getName(), '');
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
                ///////

                if ($iosFileName == "" && !$delete && isset($files['product']['ios_model_file']['name'])) {
                    $iosFileName = $files['product']['ios_model_file']['name'] ?? "";
                }

                if ($this->_file->isExists($path.$iosFileName) && $iosFileName != "") {
                    $this->_file->deleteFile($path.$iosFileName);
                }
                if (!$delete) {
                    return $this;
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        if (empty($files['product']['ios_model_file']['size'])) {
            return $this;
        }

        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(
                ['fileId' => 'product['.$this->getAttribute()->getName().']']
            );
            $uploader->setAllowedExtensions(['usdz']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);
            $object->setData($this->getAttribute()->getName(), $result['file']);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }
        
        return $this;
    }
}
