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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Model\ResourceModel\Report;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Model\ResourceModel\EarnReport;
use Exception;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Sql\ExpressionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\DateTime;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceData;

class EarnCollection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * @var ExpressionFactory
     */
    protected $expression;

    /**
     * @var \Bss\RewardPoint\Model\EarnReport
     */
    protected $earnreport;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @param ExpressionFactory $expression
     * @param PriceData $priceHelper
     * @param Data $bssHelper
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        ExpressionFactory            $expression,
        PriceData                    $priceHelper,
        \Bss\RewardPoint\Helper\Data $bssHelper,
        EntityFactoryInterface       $entityFactory,
        LoggerInterface              $logger,
        FetchStrategyInterface       $fetchStrategy,
        ManagerInterface             $eventManager,
        AdapterInterface             $connection = null,
        AbstractDb                   $resource = null
    ) {
        $this->expression = $expression;
        $this->priceHelper = $priceHelper;
        $this->bssHelper = $bssHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Filter report
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param int $customerGroup
     * @param int $website
     * @return EarnCollection
     */
    public function filterEarnReport($dateStart, $dateEnd, $customerGroup, $website)
    {
        if ($customerGroup != -1 && $customerGroup != null) {
            $this->getSelect()->join(
                ['ce' => 'customer_entity'],
                'ce.entity_id=main_table.customer_id'
            );
            $this->addFieldToFilter('group_id', $customerGroup);
        }
        if ($website != 0) {
            $this->addFieldToFilter('main_table.website_id', $website);
        }
        $this->addFieldToFilter('main_table.created_at', ['from' => $dateStart, 'to' => $dateEnd]);
        $this->getSelect()->columns(["total_earn_point" => "sum(if(point>=0, point,0))",
            "earn_report_admin_change" => "sum(if(action=0 && point>0, point,0))",
            "earn_report_registration" => "sum(if(action=1 && point>0, point,0))",
            "earn_report_birthday" => "sum(if(action=2 && point>0, point,0))",
            "earn_report_first_review" => "sum(if(action=3 && point>0, point,0))",
            "earn_report_review" => "sum(if(action=4 && point>0, point,0))",
            "earn_report_first_order" => "sum(if(action=5 && point>0, point,0))",
            "earn_report_order" => "sum(if(action=6 && point>0, point,0))",
            "earn_report_order_refund" => "sum(if(action=7 && point>0, point,0))",
            "earn_report_import" => "sum(if(action=8 && point>0, point,0))",
            "earn_report_subscribe" => "sum(if(action=9 && point>0, point,0))"
        ]);
        return $this;
    }

    /**
     * Filter in spent report
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param int $customerGroup
     * @param int $website
     * @return $this
     * @throws Exception
     */
    public function filterSpentReport($dateStart, $dateEnd, $customerGroup, $website, $currency)
    {
        if ($customerGroup != -1 && $customerGroup != null) {
            $this->getSelect()->join(
                ['ce' => 'customer_entity'],
                'ce.entity_id=main_table.customer_id',
                ['ce.group_id']
            );
            $this->addFieldToFilter('group_id', $customerGroup);
        }
        if ($website != 0) {
            $this->addFieldToFilter('website_id', $website);
        }
        $this->getSelect()->joinLeft(
            ['so' => 'sales_order'],
            'so.entity_id=main_table.action_id',
            [
                'so.grand_total',
                'so.order_currency_code',
                'so.rwp_amount'
            ]
        );
        $this->addFieldToFilter('main_table.created_at', ['from' => $dateStart, 'to' => $dateEnd]);
        $resultInterval = [
            "total_spent_point" => 0,
            "spent_report_total_order" => 0,
            "spent_report_discount" => 0,
            "spent_point_value_order" => 0,
            "spent_report_rate" => 0,
            "total_earn_point" => 0
        ];
        $listItem = $this->getItems();
        foreach ($listItem as $item) {
            if ($item->getPoint() < 0) {
                $resultInterval['total_spent_point'] -= $item->getPoint();
            }
            if ($item->getAction() >= 5 && $item->getAction() <= 7 && $item->getPoint() < 0) {
                $resultInterval['spent_report_total_order']++;
                $valueOrder = $item->getGrandTotal() ?? 0;
                $resultInterval['spent_point_value_order'] += $this->bssHelper->convertAmountBaseCurrency($valueOrder, $item['order_currency_code'], $currency);
            }
            if ($item->getAmount() > 0) {
                $resultInterval['spent_report_discount'] += $this->bssHelper->convertAmountBaseCurrency($item->getAmount(), $item['base_currrency_code'], $currency);
            }
            if ($item->getPoint() >= 0) {
                $resultInterval['total_earn_point'] += $item->getPoint();
            }
        }

        if ($resultInterval['spent_point_value_order'] != 0) {
            $resultInterval['spent_report_rate'] = round(100 *
                $resultInterval['spent_report_discount'] / $resultInterval['spent_point_value_order'], 4);
        }
        $this->removeAllItems();
        $this->addItem(new DataObject($resultInterval));
        return $this;
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\RewardPoint\Model\EarnReport::class,
            EarnReport::class
        );
    }


}
