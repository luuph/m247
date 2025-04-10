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

namespace Webkul\Walletsystem\Model\ResourceModel\Grid;

use Webkul\Walletsystem\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Webkul Walletsystem Class
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Document
     */
    protected $document = Document::class;

    /**
     * @var map
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id']];
   
    /**
     * Constructor
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction $resourceModel
     * @param null|string $identifierName
     * @param null|string $connectionName
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'wk_ws_wallet_transaction',
        $resourceModel = \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction::class,
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Render filters
     */
    protected function _renderFiltersBefore()
    {
        $this->join(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            'customer_grid_flat.entity_id = main_table.customer_id',
            ['name' => 'name', 'email' => 'email']
        );
        parent::_renderFiltersBefore();
    }
}
