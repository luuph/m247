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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Ui\Component\Listing\Column;

use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\OrderFactory;

/**
 * Class QuoteId
 *
 * @package Bss\QuoteExtension\Ui\Component\Listing\Column
 */
class OrderId extends Column
{

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param UiComponentFactory $uiComponentFactory
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface      $context,
        UrlInterface          $urlBuilder,
        UiComponentFactory    $uiComponentFactory,
        ManageQuoteRepository $manageQuoteRepository,
        OrderFactory          $orderFactory,
        array                 $components = [],
        array                 $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    if (isset($item['order_entity_id']) && isset($item['order_increment_id'])) {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->urlBuilder->getUrl(
                                    'sales/order/view',
                                    [
                                        'order_id' => $item['order_entity_id']
                                    ]
                                ),
                                'label' => __($item['order_increment_id'])
                            ]
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
