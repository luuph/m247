<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Model\ResourceModel\Import;

use ArrayIterator;
use IteratorAggregate;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\ResourceModel\Import\Data as CoreImportData;
use Traversable;
use Zend_Db;
use Zend_Db_Statement_Exception;

/**
 * Class Data
 * Mageplaza\Shopbybrand\Plugin\Model\ResourceModel\Import
 */
class Data
{
    /** @var RequestInterface */
    protected $request;

    /**
     * Constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Retrieve an external iterator
     *
     * @param CoreImportData $coreData
     * @param callable $process
     *
     * @return ArrayIterator|Traversable
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     * @throws \Exception
     */
    public function aroundGetIterator(CoreImportData $coreData, callable $process)
    {
        if ($this->request->getParam('entity') !== 'mageplaza_brand') {
            return $process();
        }

        $importIds = $this->request->getParam('_import_ids');
        if (!$importIds) {
            throw new LocalizedException(__('Import IDs are not specified.'));
        }

        $connection = $coreData->getConnection();
        $select     = $connection->select()
            ->from($coreData->getMainTable(), ['data'])
            ->order('id DESC');
        $select->where('id IN (?)', $importIds);

        $stmt = $connection->query($select);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);

        if ($stmt instanceof IteratorAggregate) {
            $iterator = $stmt->getIterator();
        } else {
            // Statement doesn't support iterating, so fetch all records and create iterator ourselves
            $rows     = $stmt->fetchAll();
            $iterator = new ArrayIterator($rows);
        }

        return $iterator;
    }
}
