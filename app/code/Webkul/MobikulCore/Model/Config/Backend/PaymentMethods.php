<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Config\Backend;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Cmspages model
 */
class PaymentMethods implements OptionSourceInterface
{
    /**
     * PaymentHelper variable
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * PaymentConfig variable
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * Payments_for_app variable
     *
     * @var array
     */
    protected $payments_for_app = [
        "free",
        "checkmo",
        "purchaseorder",
        "banktransfer",
        "cashondelivery",
        "myfatoorah_payment",
        "walletsystem"
    ];

    /**
     * Undocumented function
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        $this->_paymentHelper = $paymentHelper;
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * ToOptionArray function
     */
    public function toOptionArray()
    {
        $collection = $this->_paymentConfig->getActiveMethods();
        $returnData = [];
        foreach ($collection as $code => $method) {
            if(in_array($code, $this->payments_for_app)) {
                $returnData[] =  [
                    "value" => $code,
                    "label" => $method->getTitle() ?? $code
                ];
            }
        }
        return $returnData;
    }

    /**
     * mobikulPaymentOptionsArray function
     */
    public function mobikulPaymentOptionsArray()
    {
        $collection = $this->_paymentConfig->getActiveMethods();
        $returnData = [];
        foreach ($collection as $code => $method) {
            if(in_array($code, $this->payments_for_app)) {
                $returnData[] = $code;
            }
        }
        return $returnData;
    }
}
