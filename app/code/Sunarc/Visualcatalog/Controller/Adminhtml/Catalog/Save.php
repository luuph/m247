<?php

namespace Sunarc\Visualcatalog\Controller\Adminhtml\Catalog;

use Braintree\Exception;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;


use Magento\Framework\App\ResponseInterface;

class Save extends Action
{

    private $resultPageFactory;
    private $orderFactory;
    private $resultJsonFactory;
    protected $_storeManager;
    protected $_indexerFactory;
    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
          \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
    \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue('positions');
        //$selected = $this->getRequest()->getPostValue('selected');
       /* echo "<pre>";
        print_r(($data));
        die('here');*/
        $resultJson = $this->resultJsonFactory->create();
        $categoryId = $this->getRequest()->getParam('id'); //replace with your category id
        $category = $this->_categoryFactory->create()->load($categoryId);
        try {
            $products = $category->getProductsPosition();
            foreach ($data as $productId => $position) {
                $products[$productId] = $position;
            }
            $category->setPostedProducts($products);
            $category->save();

            $indexerCollection = $this->_indexerCollectionFactory->create();
           $ids = $indexerCollection->getAllIds();
           foreach ($ids as $id) {
               $idx = $this->_indexerFactory->create()->load($id);
                if($id=='catalog_category_product')
                {
                   $idx->reindexAll($id); 
                }
            }
            $this->messageManager->addSuccess(__('You saved the catalog positions.'));
            $response = ['error'=>0];
        } catch (\Exception $e) {
            $response = ['error'=>1];
            $this->messageManager->addError(__('Something went wrong while saving the category.'.$e->getMessage()));
        }
        return $resultJson->setData($response);
    }//end execute()
}//end class
