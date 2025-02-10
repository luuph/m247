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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History;

use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObject;

class Change extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $helperCurrency;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var int|null
     */
    private $scopeId = null;

    /**
     * Change constructor.
     *
     * @param Context $context
     * @param HelperCurrency $helperCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperCurrency $helperCurrency,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerFactory = $customerFactory;
        $this->helperCurrency = $helperCurrency;
    }

    /**
     * Render html.
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if($this->scopeId == null){
            $customerId = $this->getRequest()->getParam('id') ? : null;
            if($customerId){
                $customer = $this->customerFactory->create()->load($customerId);
                $this->scopeId = $customer->getStoreId();
            }
        }
        $class = "bss-companycredit-red";
        $value = $this->helperCurrency->formatPrice($this->_getValue($row), $row->getCurrencyCode(), $this->scopeId);
        if ($this->_getValue($row) > 0) {
            $class = 'bss-companycredit-green';
            $value = '+' . $value;
        }
        return '<span class="' . $class . '"><span>' . $value . '</span></span>';
    }
}
