<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Cart\BuyRequest;

use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;

/**
 * Class BssGiftCardDataProvider
 *
 * @package Bss\GiftCardGraphQl\Model\Cart\BuyRequest
 */
class BssGiftCardDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $cartItemData): array
    {
        if (isset($cartItemData['giftcard_options'])) {
            return [
                'giftcard_options' => $cartItemData['giftcard_options']
            ];
        } else {
            return [];
        }
    }
}
