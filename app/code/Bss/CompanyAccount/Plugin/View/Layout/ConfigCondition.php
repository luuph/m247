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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\View\Layout;

use Magento\Customer\Model\Session;

class ConfigCondition
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @param ConfigCondition $subject
     * @param bool $result
     * @return bool
     * bool
     */
    public function afterIsVisible($subject, $result, array $arguments)
    {
        if ($arguments['configPath'] == "newsletter/general/active" && $this->session->getSubUser()
        ) {
            $result = false;
        }
        return $result;
    }
}
