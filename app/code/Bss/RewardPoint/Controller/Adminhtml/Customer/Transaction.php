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
namespace Bss\RewardPoint\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Class transaction
 *
 * Bss\RewardPoint\Controller\Adminhtml\Customer
 */
class Transaction extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer newsletter grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
