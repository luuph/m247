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

namespace Bss\GiftCardGraphQl\Model\Cart;

use Bss\GiftCard\Helper\Catalog\Product\Configuration as ConfigurationGiftCard;
use Bss\GiftCard\Model\ResourceModel\Template\Image;
use Bss\GiftCard\Model\Template\ImageFactory;
use Bss\GiftCard\Model\TemplateFactory;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class CartGiftCardOption
 */
class CartGiftCardOption implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    const GIFTCARD_VALUE = 'giftcard_value';
    const GIFTCARD_SENDER_NAME = 'giftcard_sender_name';
    const GIFTCARD_SENDER_EMAIL = 'giftcard_sender_email';
    const GIFTCARD_RECIPIENT_NAME = 'giftcard_recipient_name';
    const GIFTCARD_RECIPIENT_EMAIL = 'giftcard_recipient_email';
    const GIFTCARD_TEMPLATE_NAME = 'giftcard_template_name';
    const GIFTCARD_IMAGE = 'giftcard_image';
    const GIFTCARD_MESSAGE = 'giftcard_message';
    const GIFTCARD_DELIVERY_DATE = 'giftcard_delivery_date';
    const GIFTCARD_TIMEZONE = 'giftcard_timezone';

    /**
     * @var ConfigurationGiftCard
     */
    private $configurationCard;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * CartGiftCardOption constructor.
     *
     * @param TemplateFactory $templateFactory
     * @param ImageFactory $imageFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ConfigurationGiftCard $configurationCard
     */
    public function __construct(
        TemplateFactory $templateFactory,
        ImageFactory $imageFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        ConfigurationGiftCard $configurationCard
    ) {
        $this->configurationCard = $configurationCard;
        $this->templateFactory = $templateFactory;
        $this->imageFactory = $imageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ItemInterface $item */
        $item = $value['model'];
        $options = $this->configurationCard->getGiftCardOptions($item, false);
        return $this->formatResult($options);
    }

    /**
     * Get format option result
     *
     * @param array $options
     * @return array
     */
    public function formatResult($options)
    {
        $result = [];
        if (isset($options[ConfigurationGiftCard::GIFTCARD_AMOUNT])) {
            if ($options[ConfigurationGiftCard::GIFTCARD_AMOUNT] == "custom") {
                $result[self::GIFTCARD_VALUE] = $options[ConfigurationGiftCard::GIFTCARD_AMOUNT_DYNAMIC];
            } else {
                $result[self::GIFTCARD_VALUE] = $this->configurationCard->renderAmount($options) ?? null;
            }
        }

        if (isset($options[ConfigurationGiftCard::GIFTCARD_TEMPLATE])) {
            $template = $this->templateFactory->create()->load($options[ConfigurationGiftCard::GIFTCARD_TEMPLATE]);
            $result[self::GIFTCARD_TEMPLATE_NAME] = $template->getName();
        }
        $result[self::GIFTCARD_SENDER_NAME] = $options[ConfigurationGiftCard::GIFTCARD_SENDER_NAME] ?? null;
        $result[self::GIFTCARD_RECIPIENT_NAME] = $options[ConfigurationGiftCard::GIFTCARD_RECIPIENT_NAME] ?? null;
        $result[self::GIFTCARD_SENDER_EMAIL] = $options[ConfigurationGiftCard::GIFTCARD_SENDER_EMAIL] ?? null;
        $result[self::GIFTCARD_RECIPIENT_EMAIL] = $options[ConfigurationGiftCard::GIFTCARD_RECIPIENT_EMAIL] ?? null;
        $result[self::GIFTCARD_MESSAGE] = $options[ConfigurationGiftCard::GIFTCARD_MESSAGE_EMAIL] ?? null;
        $result[self::GIFTCARD_DELIVERY_DATE] = $options[ConfigurationGiftCard::GIFTCARD_DELIVERY_DATE] ?? null;
        $result[self::GIFTCARD_TIMEZONE] = $options[ConfigurationGiftCard::GIFTCARD_TIMEZONE] ?? null;
        if (isset($options[ConfigurationGiftCard::GIFTCARD_IMAGE])) {
            $imageId = $options[ConfigurationGiftCard::GIFTCARD_IMAGE];
            $image = $this->imageFactory->create()->load($imageId);
            $imageAbsolutePath = $image->getValue();
            $imageData = [];
            if ($imageAbsolutePath) {
                $imageData = [
                    'origin' => $image->getResource()->resize($imageAbsolutePath),
                    'base' => $image->getResource()->resize(
                        $imageAbsolutePath,
                        Image::BASE_IMG_WIDTH,
                        Image::BASE_IMG_HEIGHT
                    ),
                    'thumbnail' => $image->getResource()->resize(
                        $imageAbsolutePath,
                        Image::THUMBNAIL_IMG_WIDTH,
                        Image::THUMBNAIL_IMG_HEIGHT
                    )
                ];
            }
            $result[self::GIFTCARD_IMAGE] = $imageData;
        }

        return $result;
    }
}
