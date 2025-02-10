<?php

namespace Biztech\Translator\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ProductFactory;
use Biztech\Translator\Helper\Data;

/**
 * Calculatechar class is used to calculate Total charactors of all product with selected attribute inside configuration.
 */
class Calculatechar extends Action
{


    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Biztech\Translator\Helper\Data
     */
    protected $helper;
    /**
     * getting product details.
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Collection $productCollection
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Collection $productCollection,
        ProductFactory $productFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_productCollection = $productCollection;
        $this->_productFactory = $productFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $charCount = 0;
        $charCountwithoutHtml = 0;
        $products = $this->_productCollection->load();

        $attributes = $this->helper->getConfigValue('translator/general/massaction_product_translate_fields');
        $translateAll = $this->helper->getConfigValue('translator/general/translate_all');
        $finalAttributeSet = array_values(explode(',', $attributes));
        
        foreach ($products as $p) {
            $productModel = $this->_productFactory->create();
            $product = $productModel->load($p->getEntityId());
            if (($translateAll == 1 && $product->getTranslated() == 1) || ($translateAll == 1 && $product->getTranslated() == 0) || ($translateAll == 0 && $product->getTranslated() == 0)) {
                foreach ($finalAttributeSet as $attributeCode) {
                    if (!isset($product[$attributeCode]) || empty($product[$attributeCode])) {
                        continue;
                    } else {
                        $char = strip_tags($product[$attributeCode]);
                        $charCount += mb_strlen($product[$attributeCode]);
                        $charCountwithoutHtml += mb_strlen($char);
                    }
                }
            }
        }

        $resultData = [
            'withouthtml' => $charCountwithoutHtml,
            'withhtml' => $charCount
        ];

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData($resultData);
    }
}
