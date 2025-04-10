<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Controller\Adminhtml\Profile;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory; 

class LoadProduct extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context, 
        CollectionFactory $collectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
    ){
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $sku = $this->getRequest()->getParam('sku');

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter('sku', ['like' => '%' . $sku . '%']);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $listSku = [];
        foreach ($collection as $product) {
            $listSku[] = [
                'value' => $product->getSku(),
                'name' => $product->getName()
            ];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($listSku);
    }
}
