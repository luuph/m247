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

use Bss\QuoteExtension\Model\Url;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class QuoteId
 *
 * @package Bss\QuoteExtension\Ui\Component\Listing\Column
 */
class QuoteId extends Column
{

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Url
     */
    protected $quoteExtensionUrl;

    /**
     * @param ContextInterface $context
     * @param Url $quoteExtensionUrl
     * @param UiComponentFactory $uiComponentFactory
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface         $context,
        Url                      $quoteExtensionUrl,
        UiComponentFactory       $uiComponentFactory,
        ManageQuoteRepository    $manageQuoteRepository,
        OrderRepositoryInterface $orderRepository,
        array                    $components = [],
        array                    $data = []
    ) {
        $this->quoteExtensionUrl = $quoteExtensionUrl;
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->orderRepository = $orderRepository;
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
                if (isset($item['quote_extension_entity_id'])) {
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->quoteExtensionUrl->getQuoteExtensionBackendViewUrl(
                                $item['quote_extension_entity_id']
                            ),
                            'label' => __($item['quote_extension_increment_id'])
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
