<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\Plugin\Checks;

/**
 * Webkul Walletsystem Class
 */
class ZeroTotal
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->walletHelper = $walletHelper;
    }

    /**
     * After Is Applicable
     *
     * @param \Magento\Payment\Model\Checks\ZeroTotal $subject
     * @param collection $result
     * @return array
     */
    public function afterIsApplicable(
        \Magento\Payment\Model\Checks\ZeroTotal $subject,
        $result
    ) {
        if (!$result) {
            return $this->walletHelper->getPaymentisEnabled();
        }
        return $result;
    }
}
