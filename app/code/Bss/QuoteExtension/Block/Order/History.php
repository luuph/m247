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

use Bss\QuoteExtension\Model\Url;
use Magento\Framework\View\Element\Template;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\View\Element\Template\Context;

class History extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Bss_QuoteExtension::order/history/table-column.phtml';

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var Url
     */
    protected $quoteExtensionUrl;

    /**
     * @param Context $context
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param Url $quoteExtensionUrl
     * @param array $data
     */
    public function __construct(
        Template\Context      $context,
        ManageQuoteRepository $manageQuoteRepository,
        Url                   $quoteExtensionUrl,
        array                 $data = []
    ) {
        parent::__construct($context, $data);
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->quoteExtensionUrl = $quoteExtensionUrl;
    }

    /**
     * Get quote by order id
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     */
    public function getQuoteExtension()
    {
        return $this->manageQuoteRepository->getByOrder($this->getOrder());
    }

    /**
     * Get quote by order id
     *
     * @return string
     */
    public function getQuoteExtensionUrl()
    {
        return $this->quoteExtensionUrl->getQuoteExtensionFrontendViewUrl($this->getQuoteExtension()->getEntityId());
    }
}
