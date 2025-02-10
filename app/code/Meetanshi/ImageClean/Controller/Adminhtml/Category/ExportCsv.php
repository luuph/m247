<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Category;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Meetanshi\ImageClean\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Ui\Model\Export\MetadataProvider;
use Meetanshi\ImageClean\Model\ResourceModel\Images;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ExportCsv extends \Magento\Backend\App\Action
{
    protected $resultForwardFactory;
    protected $filter;
    protected $metadataProvider;
    protected $directory;
    protected $converter;
    protected $fileFactory;
    protected $collectionFactory;
    protected $resources;
    protected $connection;
    protected $date;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        Filter $filter,
        Filesystem $filesystem,
        ConvertToCsv $converter,
        FileFactory $fileFactory,
        MetadataProvider $metadataProvider,
        Images $resource,
        CollectionFactory $collectionFactory,
        DateTime $date
    )
    {
        $this->resources = $resource;
        $this->filter = $filter;
        $this->connection = $this->resources->getConnection();
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->date = $date;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        try {
            $fileName = 'unUseCategoryImages.csv';
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            if ($collection->getSize() > 0) {
                $component = $this->filter->getComponent();
                $this->filter->prepareComponent($component);
                $dataProvider = $component->getContext()->getDataProvider();
                $dataProvider->setLimit(0, false);
                $ids = [];

                foreach ($collection as $document) {
                    $ids[] = (int)$document->getId();
                }

                $searchResult = $component->getContext()->getDataProvider()->getSearchResult();
                $fields = $this->metadataProvider->getFields($component);
                $options = $this->metadataProvider->getOptions();
                $name = $this->date->date('Y-m-d_H-i-s');
                $file = 'export/' . $component->getName() . $name . '.csv';
                $this->directory->create('export');
                $stream = $this->directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($this->metadataProvider->getHeaders($component));
                foreach ($searchResult->getItems() as $document) {
                    if (in_array($document->getId(), $ids)) {
                        $this->metadataProvider->convertDate($document, $component->getName());
                        $stream->writeCsv($this->getRowData($document, $fields, $options));
                    }
                }
                $stream->unlock();
                $stream->close();
                return $this->fileFactory->create($fileName, [
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true
                ], 'var');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/images/category');
        }
    }

    public function getRowData(DocumentInterface $document, $fields, $options)
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } elseif($column == 'filename') {
                $mediaPath = getcwd() . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category';

                $row[] = $mediaPath.$document->getCustomAttribute($column)->getValue();
            }else{
                $row[] = $document->getCustomAttribute($column)->getValue();
            }
        }
        return $row;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}