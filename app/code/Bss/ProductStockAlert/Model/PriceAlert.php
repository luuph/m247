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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model;

class PriceAlert extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\ProductStockAlert\Model\ResourceModel\PriceAlert::class);
    }

    /**
     * Load object data by parameters
     *
     * @return $this
     */
    public function loadByParam()
    {
        if ($this->getProductId() !== null && $this->getCustomerId() !== null && $this->getWebsiteId() !== null) {
            $this->getResource()->loadByParam($this);
        }
        return $this;
    }

    /**
     * Load object data by parameters
     *
     * @return $this
     */
    public function loadByParamGuest()
    {
        if ($this->getProductId() !== null && $this->getCustomerEmail() !== null && $this->getWebsiteId() !== null) {
            $this->getResource()->loadByParamGuest($this);
        }
        return $this;
    }

    /**
     * Delete customer.
     *
     * @param string $customerEmail
     * @param int $websiteId
     * @return $this
     */
    public function deleteCustomer($customerEmail, $websiteId = 0)
    {
        $this->getResource()->deleteCustomer($customerEmail, $websiteId);
        return $this;
    }

    /**
     * Has email
     *
     * @param int $customerId
     * @param int $productId
     * @param int $websiteId
     * @return mixed
     */
    public function hasEmail($customerId, $productId, $websiteId)
    {
        return $this->getResource()->hasEmail($customerId, $productId, $websiteId);
    }
}
