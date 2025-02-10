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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductActions
 */
class CustomerGroups extends Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Bss\RewardPoint\Model\Config\Source\CustomerGroups
     */
    protected $customerGroups;

    /**
     * CustomerGroups constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Bss\RewardPoint\Model\Config\Source\CustomerGroups $customerGroups
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Bss\RewardPoint\Model\Config\Source\CustomerGroups $customerGroups,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->customerGroups = $customerGroups;
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
                $customerGroups = $this->customerGroups->toArray();
                $item[$this->getData('name')] = $this->getCustomerGroup($customerGroups, $item);
            }
        }
        return $dataSource;
    }

    /**
     * Get customer group
     *
     * @param array $customerGroups
     * @param array $item
     * @return string
     */
    protected function getCustomerGroup($customerGroups, $item)
    {
        if (isset($customerGroups[$item['customer_group_id']])) {
            return $customerGroups[$item['customer_group_id']];
        }
        return '-';
    }
}
