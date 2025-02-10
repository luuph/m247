<?php

namespace Sunarc\Visualcatalog\Controller\Adminhtml\Catalog;

use Braintree\Exception;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;


use Magento\Framework\App\ResponseInterface;

class Move extends Action
{

    private $resultPageFactory;
    private $orderFactory;
    private $resultJsonFactory;

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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_categoryFactory = $categoryFactory;
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
        $selected = $this->getRequest()->getPostValue('selected');
        $current_page = $this->getRequest()->getPostValue('current_page');
        $selected_product_id ='';
        $selected_product_position ='';
        $resultJson = $this->resultJsonFactory->create();
        if(!empty($selected))
        {
        foreach($selected as $key => $value){
          $selected_product_id = $key;
        $selected_product_position = $value;  
        }

        $page = 1;
        if($this->getRequest()->getPostValue('page')){
        $page = $this->getRequest()->getPostValue('page');}
       
        
        $categoryId = $this->getRequest()->getParam('id'); //replace with your category id
        $category = $this->_categoryFactory->create()->load($categoryId);
         if($current_page < $page){
        $limit = (count($data)*($page-1))+1;
        $product_list = $category->getProductCollection()->addAttributeToSort('position', 'ASC')->setPageSize($limit)->setCurPage(1);
        $product_list = $product_list->getData();

        $product_data = array();
        foreach($product_list as $k1 => $product){
            if ($k1 >= $selected_product_position){
            $product_data[$product['entity_id']]= $k1;}
        }
        $product_data[$selected_product_id]= $limit;
    }

  if($current_page > $page){
        $limit = (10 *($current_page-1))+$selected_product_position;
       // echo "position :".$selected_product_position."limit  :  ".$limit; 
       // $limit = (count($data)*($page-1))+1;
        $product_list = $category->getProductCollection()->addAttributeToSort('position', 'ASC')->setPageSize($limit)->setCurPage(1);
        $product_list = $product_list->getData();
       /* echo "<pre>";
print_r($product_list); */
        $product_data = array();
       
        foreach($product_list as $k1 => $product){
            if (($k1+1) >= (10 *($page))){
            $product_data[$product['entity_id']]= $k1+2;}
        }
        $product_data[$selected_product_id]= (10*($page));
    }

// print_r($product_data); die();
     /*   print_r($product_data);
        die();
        die();*/
        try {
            $products = $category->getProductsPosition();
            foreach ($product_data as $productId => $position) {
                $products[$productId] = $position;
            }
            $category->setPostedProducts($products);
            $category->save();
            $this->messageManager->addSuccess(__('You saved the catalog positions.'));
            $response = ['error'=>0];
        } catch (\Exception $e) {
            $response = ['error'=>1];
            $this->messageManager->addError(__('Something went wrong while saving the category.'.$e->getMessage()));
        }
        return $resultJson->setData($response);
    }
    else{
            $response = ['error'=>1];
            $this->messageManager->addError(__('Please select an item.'));
        
        return $resultJson->setData($response);

    } // endif
    }//end execute()

}//end class
