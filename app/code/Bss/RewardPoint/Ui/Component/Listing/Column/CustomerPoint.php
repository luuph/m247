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
namespace Bss\RewardPoint\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class CustomerPoint extends Column
{
    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\CustomerGroups
     */
    protected $customerGroups;

    /**
     * CustomerPoint constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\System\Store $systemStore,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->systemStore = $systemStore;
        $this->transactionFactory = $transactionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $transaction = $this->transactionFactory->create();
                $points = '';
                $websites = $this->systemStore->getWebsiteOptionHash();
                foreach ($websites as $websiteId => $label) {
                    $point = $transaction->loadByCustomer($item['entity_id'], $websiteId)->getPointBalance();
                    if ($point) {
                        $points .= '<strong>' . $label . '</strong>: ' . $point . ' point </br>';
                    }
                }
                $item[$this->getData('name')] = $points;
            }
        }
        return $dataSource;
    }
}
