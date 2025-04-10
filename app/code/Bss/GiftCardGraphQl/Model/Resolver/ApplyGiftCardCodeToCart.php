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
 * @copyright   Copyright © 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Model\GiftCard;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

class ApplyGiftCardCodeToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var GiftCard
     */
    protected $giftCard;

    /**
     * @param GetCartForUser $getCartForUser
     * @param GiftCard $giftCard
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        GiftCard $giftCard
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->giftCard = $giftCard;
    }

    /**
     * Resolve
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['masked_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['masked_cart_id'];

        if (empty($args['input']['gift_card_code'])) {
            throw new GraphQlInputException(__('Required parameter "gift_card_code" is missing'));
        }
        $giftCardCode = $args['input']['gift_card_code'];

        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        if (!$cart->getItemsCount()) {
            throw new GraphQlNoSuchEntityException(__('Cart %1 doesn\'t contain products', $cart->getId()));
        }
        $this->giftCard->applyGiftCode($cart, $giftCardCode);
        return [
            'success' => true,
            'message' => 'Gift Cart Code Successfully Applied To Cart',
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
