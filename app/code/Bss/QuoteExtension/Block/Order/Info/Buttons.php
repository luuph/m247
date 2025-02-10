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

namespace Bss\QuoteExtension\Block\Order\Info;

use Bss\QuoteExtension\Model\Url;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var string
     */
    protected $_template = 'Bss_QuoteExtension::order/info/buttons.phtml';

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
     * @param Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param Url $quoteExtensionUrl
     * @param array $data
     */
    public function __construct(
        Context                             $context,
        Registry                            $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        ManageQuoteRepository               $manageQuoteRepository,
        Url                                 $quoteExtensionUrl,
        array                               $data = []
    ) {
        parent::__construct($context, $registry, $httpContext, $data);
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->quoteExtensionUrl = $quoteExtensionUrl;
    }

    /**
     * Get url for reorder action
     *
     * @return string
     */
    public function getRelatedQuoteUrl()
    {
        return $this->quoteExtensionUrl->getQuoteExtensionFrontendViewUrl($this->getQuoteExtension()->getEntityId());
    }

    /**
     * Get quote extension id by quote id
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote|null
     */
    public function getQuoteExtension()
    {
        return $this->manageQuoteRepository->getByOrder($this->getOrder());
    }

    /**
     * Get quote extension increment id
     *
     * @return array|int|mixed|null
     */
    public function getQuoteExtensionIncrementId()
    {
        return $this->getQuoteExtension()->getIncrementId();
    }
}
