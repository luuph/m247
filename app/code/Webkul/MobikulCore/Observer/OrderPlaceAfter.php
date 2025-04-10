<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * OrderPlaceAfter class observer to set order purchase point
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * MobikulHelper variable
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $mobikulHelper;

    /**
     * UrlInterface variable
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Constructor function
     *
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Webkul\MobikulCore\Helper\Data $mobikulHelper
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\MobikulCore\Helper\Data $mobikulHelper
    ) {
        $this->mobikulHelper = $mobikulHelper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Execute function to set purchase point
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $url = $this->urlInterface->getCurrentUrl();
        if (stripos($url, "mobikulhttp") === false && $order) {
            $this->mobikulHelper->setPurchasePoint('web',$order);
        }
    }
}
