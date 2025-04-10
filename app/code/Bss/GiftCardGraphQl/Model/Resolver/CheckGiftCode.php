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
 * @copyright   Copyright © 2023-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Resolver;

class CheckGiftCode implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var \Bss\GiftCard\Helper\Data
     */
    protected $giftCardData;

    /**
     * @var \Bss\GiftCard\Model\Pattern\CodeFactory
     */
    protected $codeFactory;

    /**
     * Construct
     *
     * @param \Bss\GiftCard\Helper\Data $giftCardData
     * @param \Bss\GiftCard\Model\Pattern\CodeFactory $codeFactory
     */
    public function __construct(
        \Bss\GiftCard\Helper\Data $giftCardData,
        \Bss\GiftCard\Model\Pattern\CodeFactory $codeFactory
    ) {
        $this->giftCardData = $giftCardData;
        $this->codeFactory = $codeFactory;
    }

    /**
     * Check and get gift code information
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     */
    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            if (!isset($args['giftCode']) && !$args['giftCode']) {
                throw new \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException(__('giftCode is required!'));
            }
            $giftCode = $args['giftCode'];
            $giftCard = $this->codeFactory->create()->loadByCode($giftCode);
            if ($giftCard->getId()) {
                return [
                    'code' => $giftCode,
                    'value' => $this->giftCardData->convertPrice($giftCard->getValue()),
                    'origin_value' => $this->giftCardData->convertPrice($giftCard->getOriginValue()),
                    'status' => $giftCard->getStatusLabel(),
                    'created_at' => $this->giftCardData->formatDateTime($giftCard->getCreatedTime()),
                    'updated_at' => $this->giftCardData->formatDateTime($giftCard->getUpdatedTime())
                ];
            } else {
                throw new \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException(
                    __('The gift card code "%1" is not valid.', $giftCode)
                );
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException(__($e->getMessage()));
        }
    }
}
