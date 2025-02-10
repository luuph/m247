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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Form;

class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @return false
     */
    public function checkSubUser()
    {
        $subUser = $this->customerSession->getSubUser();
        if ($subUser && $subUser->getSubStatus()) {
            return $subUser;
        }
        return false;
    }
}
