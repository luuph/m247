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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Ui\Component\Listing\Column\Customer;

use Bss\SalesRep\Helper\Data as HelperData;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Ui\Component\Listing\Column\Customer
 */
class CompanyAccount extends Column
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * CompanyAccount constructor.
     * @param HelperData $helperData
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        HelperData $helperData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Set Is Company Account in DataSource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item["bss_is_company_account"]) && isset($item["bss_is_company_account"][0])) {
                    if ($item["bss_is_company_account"][0] == 1) {
                        $item[$this->getData('name')] = __("Yes");
                    } else {
                        $item[$this->getData('name')] = __("No");
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * Display column Is Company Account
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->helperData->isEnableCompanyAccount()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
