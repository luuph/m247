<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Model\Order;
use \Webkul\MobikulCore\Helper\Data;
 
class OrderPlaceFrom extends Column
{
    /**
     * OrderRepository variable
     *
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    
    /**
     * SearchCriteria variable
     *
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;
    
    /**
     * Customfactory variable
     *
     * @var Order
     */
    protected $_customfactory;
    
    /**
     * Helper variable
     *
     * @var Data
     */
    protected $_helper;
 
    /**
     * Construct function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param Order $customFactory
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        Order $customFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_customfactory = $customFactory;
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * PrepareDataSource function
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $order  = $this->_orderRepository->get($item["entity_id"]);
                    $order_id = $order->getEntityId();
                    $collection = $this->_customfactory->create()->getCollection();
                    $collection->addFieldToFilter('entity_id', $order_id);
                    $data = $collection->getFirstItem();
                    $item[$this->getData('name')] = $data->getPurchasePoint();
                } catch (\Exception $e) {
                    $this->_helper->printLog($e->getMessage());
                }

            }
        }
        return $dataSource;
    }
}
