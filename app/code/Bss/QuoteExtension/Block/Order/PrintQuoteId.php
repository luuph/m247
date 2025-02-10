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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Block\Order;

use Magento\Framework\Registry;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;

class PrintQuoteId extends \Magento\Sales\Block\Order\PrintShipment
{
    /**
     * @var string
     */
    protected $_template = 'Bss_QuoteExtension::order/print/quote-id.phtml';

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $paymentHelper
     * @param Renderer $addressRenderer
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry                      $registry,
        \Magento\Payment\Helper\Data                     $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer      $addressRenderer,
        ManageQuoteRepository                            $manageQuoteRepository,
        array                                            $data = []
    ) {
        $this->manageQuoteRepository = $manageQuoteRepository;
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
    }

    /**
     * Get quote extension increment id by order
     *
     * @return array|int|mixed|null
     */
    public function getQuoteExtensionIncrementId()
    {
        return $this->manageQuoteRepository->getByOrder($this->getOrder())->getIncrementId();
    }
}
