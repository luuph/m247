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
 * @copyright  Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Model;

use Magento\Framework\UrlInterface;

class Url
{
    private const ROUTE_QUOTE_EXTENSION_FRONTEND_VIEW = 'quoteextension/quote/view';
    private const ROUTE_QUOTE_EXTENSION_BACKEND_VIEW = 'bss_quote_extension/manage/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get quote extension frontend view url
     *
     * @param int $quoteExtensionId
     * @return string
     */
    public function getQuoteExtensionFrontendViewUrl($quoteExtensionId)
    {
        if ($quoteExtensionId != null) {
            return $this->urlBuilder->getUrl(
                self::ROUTE_QUOTE_EXTENSION_FRONTEND_VIEW,
                ['quote_id' => $quoteExtensionId]
            );
        }
        return null;
    }

    /**
     * Get quote extension backend view url
     *
     * @param int $quoteExtensionId
     * @return string
     */
    public function getQuoteExtensionBackendViewUrl($quoteExtensionId)
    {
        if ($quoteExtensionId != null) {
            return $this->urlBuilder->getUrl(
                self::ROUTE_QUOTE_EXTENSION_BACKEND_VIEW,
                ['entity_id' => $quoteExtensionId]
            );
        }
        return null;
    }
}
