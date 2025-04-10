<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AppliedGiftCardCodes implements ResolverInterface
{
    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @param QuoteFactory $giftCardQuoteFactory
     */
    public function __construct(
        QuoteFactory $giftCardQuoteFactory
    ) {
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $cartId = $cart->getId();
        $giftCardQuote = $this->giftCardQuoteFactory->create();
        $giftCardCodes = $giftCardQuote->getGiftCardCode($cart);
        $appliedCodes = [];
        foreach ($giftCardCodes as $giftCardCode) {
            $appliedCodes[] = [
                "id" => $giftCardCode['id'],
                "quote_id" => $giftCardCode['quote_id'],
                "giftcard_code" => $giftCardCode['giftcard_code'],
                "base_giftcard_amount" => $giftCardCode['base_giftcard_amount'],
                "giftcard_amount" => $giftCardCode['giftcard_amount']
            ];
        }
        return $appliedCodes;
    }
}
