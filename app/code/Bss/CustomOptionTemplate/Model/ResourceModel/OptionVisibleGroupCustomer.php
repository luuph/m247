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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Message\ManagerInterface;

class OptionVisibleGroupCustomer extends AbstractDb
{
    const LIMIT_ROW = 500;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * OptionVisibleGroupCustomer constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        $connectionName = null
    ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_visible_custom_option_group_customer', 'id');
    }

    /**
     * @param int $optionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVisibleOptionForGroupCustomer($optionId)
    {
        $bind = ['option_id' => $optionId];
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['visible_for_group_customer']
        )->where(
            'option_id = :option_id'
        );
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @return array
     */
    public function getListAllOptionId()
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_option'),
            ['option_id']
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param array $visibleData
     */
    public function insertCustomerAndStoreVisibility($visibleData)
    {
        try {
            if (!empty($this->getListAllOptionId())) {
                $optionCustomerVisibleData = [];
                $optionStoreVisibleData = [];
                foreach ($this->getListAllOptionId() as $optionId) {
                    $optionCustomerVisibleData[] = [
                        'option_id' => $optionId,
                        'visible_for_group_customer' => $visibleData['customer']
                    ];
                    $optionStoreVisibleData[] = [
                        'option_id' => $optionId,
                        'visible_for_store_view' => $visibleData['store']
                    ];
                }
                // insert customer
                $this->getConnection()->insertMultiple(
                    $this->getMainTable(),
                    $optionCustomerVisibleData
                );
                // insert store
                $this->getConnection()->insertMultiple(
                    $this->getTable('bss_visible_custom_option_storeview'),
                    $optionStoreVisibleData
                );
            }
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exception->getMessage()), $exception);
        }
    }

    /**
     * @return array
     */
    public function getListOptionIdSetVisible()
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_visible_custom_option_group_customer'),
            ['option_id']
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param array $optionIds
     * @return array
     */
    public function getVisibleAllOptionData($optionIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_visible_custom_option_group_customer'),
            ['option_id', 'visible_for_group_customer']
        )->where('option_id  IN (?)', $optionIds);
        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @param int $customerGroupId
     */
    public function updateNewCustomerGroupForOptions($customerGroupId)
    {
        $connection = $this->getConnection();
        $allOption = $this->getListOptionIdSetVisible();
        $totalOptions = count($allOption);
        $row = ceil($totalOptions/self::LIMIT_ROW);
        try {
            for ($index = 0; $index < $row; $index ++) {
                $arrAllOption = array_slice($allOption, $index * self::LIMIT_ROW, self::LIMIT_ROW);
                $allOptionIds = array_values($arrAllOption);
                $updateData = $this->getVisibleAllOptionData($allOptionIds);
                $conditions = [];
                foreach ($updateData as $id => $value) {
                    if ($value != null && $value != '') {
                        $value = explode(",", $value);
                        if (!in_array($customerGroupId, $value)) {
                            $value[] = $customerGroupId;
                        }
                        $value = implode(",", $value);
                    } else {
                        $value = $customerGroupId;
                    }
                    $case = $connection->quoteInto('?', $id);
                    $result = $connection->quoteInto('?', $value);
                    $conditions[$case] = $result;
                }
                $value = $connection->getCaseSql('option_id', $conditions, 'visible_for_group_customer');
                $where = ['option_id IN (?)' => array_keys($updateData)];

                $connection->beginTransaction();
                $connection->update(
                    $this->getTable('bss_visible_custom_option_group_customer'),
                    ['visible_for_group_customer' => $value],
                    $where
                );
                $connection->commit();
            }
            $this->messageManager->addSuccessMessage(__('New customer groups have been set for all products'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong with assign customer for all product,please check exception.log')
            );
            $this->logger->critical($e->getMessage());
            $connection->rollBack();
        }
    }
}
