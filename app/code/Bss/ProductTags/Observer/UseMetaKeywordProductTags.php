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
 * @package    Bss_ProductTags
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Observer;

use Bss\ProductTags\Model\Indexer\Protag;
use Magento\Framework\Event\ObserverInterface;

class UseMetaKeywordProductTags implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var Protag
     */
    protected $protag;

    /**
     * UseMetaKeywordProductTags constructor.
     *
     * @param Protag $protag
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Bss\ProductTags\Helper\Data $helper
     */
    public function __construct(
        \Bss\ProductTags\Model\Indexer\Protag $protag,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Bss\ProductTags\Helper\Data $helper
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->helper = $helper;
        $this->protag = $protag;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->getConfig('general/enable')) {
            $indexer = $this->indexerRegistry->get(\Bss\ProductTags\Model\Indexer\Protag::INDEXER_ID);
            if (!$indexer->isScheduled() && $indexer->isValid()) {
                $indexer->reindexRow((int)$product->getId());
            }
            $this->protag->executeRow($product->getId());
        }
    }
}
