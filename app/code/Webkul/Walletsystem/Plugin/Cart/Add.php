<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Plugin\Cart;

/**
 * Webkul Walletsystem Class
 */
class Add
{
    /**
     * @var  \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    protected $sourceCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;
    /**
     * @var \Magento\InventoryCatalog\Model\SourceItemsProcessor
     */
    protected $sourceItemsProcessor;
    /**
     * Constructor
     *
     * @param \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\InventoryCatalog\Model\SourceItemsProcessor $sourceItemsProcessor
     */
    public function __construct(
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\InventoryCatalog\Model\SourceItemsProcessor $sourceItemsProcessor
    ) {
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
    }

    /**
     * Before plugin for addbutton function to change Invoice Button Label
     *
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @return boolean
     */
    public function beforeExecute(
        \Magento\Checkout\Controller\Cart\Add $subject
    ) {
        $sourceListArr = $this->sourceCollectionFactory->create()->load();
        $productId = $subject->getRequest()->getParam('product');
        $sku = $this->getSkuById($productId);
        if ($sku == \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU) {
            $i=1;
            $sourceList = [];
            foreach ($sourceListArr as $sourceItemName) {
                $sourceCode = $sourceItemName->getSourceCode();
                $sourceName = $sourceItemName->getName();
                $sourceList['source_code'] = $sourceCode;
                $sourceList['status'] = 1;
                $sourceAllList[] = $sourceList;
            }
            if (!empty($sourceAllList)) {
                $this->sourceItemsProcessor->execute(
                    $sku,
                    $sourceAllList
                );
            }
        }
        return true;
    }
    /**
     * Get ProductBy Id
     *
     * @param int $id
     * @return string
     */
    public function getSkuById($id)
    {
        $product = $this->_productRepository->getById($id);
        return $product->getSku();
    }
}
