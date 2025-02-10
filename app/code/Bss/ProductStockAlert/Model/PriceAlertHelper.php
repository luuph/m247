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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model;

use Magento\Framework\App\ActionInterface;

class PriceAlertHelper
{
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $encoder;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Construct
     *
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->encoder = $encoder;
        $this->url = $url;
    }

    /**
     * Get url post action add price alert.
     *
     * @param string $productId
     * @param string|null $parentId
     * @return string
     */
    public function getAddPostAction($productId, $parentId = null)
    {
        return $this->url->getUrl(
            'productstockalert/add/pricealert',
            [
                'product_id' => $productId,
                'parent_id' => $parentId,
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl(
                    $productId,
                    'productstockalert/add/pricealert'
                )
            ]
        );
    }

    /**
     * Get url post action cancel price alert.
     *
     * @param string $productId
     * @param string|null $parentId
     * @return string
     */
    public function getCancelPostAction($productId, $parentId = null)
    {
        return $this->url->getUrl(
            'productstockalert/unsubscribe/pricealert',
            [
                'product_id' => $productId,
                'parent_id' => $parentId,
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl(
                    $productId,
                    'productstockalert/unsubscribe/pricealert'
                )
            ]
        );
    }

    /**
     * Get url list all price alert
     *
     * @return string
     */
    public function getUrlListPriceAlert()
    {
        return $this->url->getUrl('productstockalert/pricealert');
    }

    /**
     * Get url form add to cart in email template
     *
     * @return string
     */
    public function getAddToCartAction()
    {
        return $this->url->getUrl(
            'productstockalert/cart/addtocart'
        );
    }

    /**
     * Get encode url
     *
     * @param string $pid
     * @param string $path
     * @param string|null $url
     * @return string
     */
    public function getEncodedUrl($pid, $path, $url = null)
    {
        if (!$url) {
            $url = $this->url->getUrl(
                $path,
                [
                    'product_id' => $pid
                ]
            );
        }
        return $this->encoder->encode($url);
    }
}
