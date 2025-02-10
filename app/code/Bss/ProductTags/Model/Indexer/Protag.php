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
namespace Bss\ProductTags\Model\Indexer;

class Protag implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'bss_product_tag';

    /**
     * @var IndexStructure
     */
    protected $structure;

    /**
     * Protag constructor.
     * @param IndexStructure $structure
     */
    public function __construct(
        IndexStructure $structure
    ) {
        $this->structure = $structure;
    }

    /**
     * Used by mview, allows process indexer in the "Update on schedule" mode
     *
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function execute($ids)
    {
        if (!empty($ids["OldProductIds"]) && $ids["currentProductIds"]) {
            $where = ['product_id IN (?)' => $ids["OldProductIds"]];
            $this->structure->deleteData($where);
            $this->structure->indexDataToProductTagTableIndex($ids["currentProductIds"]);
        } else {
            $where = ['product_id IN (?)' => $ids];
            $this->structure->deleteData($where);
            $this->structure->indexDataToProductTagTableIndex($ids);
        }
    }

    /**
     * Will take all of the data and reindex
     * Will run when reindex via command line
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function executeFull()
    {
        $this->structure->deleteData();
        $this->structure->indexDataToProductTagTableIndex();
    }

    /**
     * Works with a set of entity changed (may be massaction)
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Works in runtime for a single entity using plugin
     *
     * @param int[] $id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function executeRow($id)
    {
        $this->execute($id);
    }
}
