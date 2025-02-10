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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Product;

use Bss\RewardPoint\Helper\Data;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Customize Product List Common
 *
 * Bss\ProductLabel\Plugin
 */
class CustomizeProductListCommon
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * CustomizeProductListCommon constructor.
     *
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Data $helper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * To html
     *
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToHtml(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $result)
    {
        $isShowingPointInCategory = $this->helper->getFlagConfig(
            Data::XML_PATH_CATE_PAGE_REWARD_POINT,
            ScopeInterface::SCOPE_STORE
        );
        if (!$this->helper->isActive() || !$isShowingPointInCategory) {
            return $result;
        }

        $product = $subject->getSaleableItem();
        $block = $this->layoutFactory->create()
            ->createBlock(\Bss\RewardPoint\Block\Customer\PointShowing::class)
            ->setTemplate('Bss_RewardPoint::account/rewardpoint-message.phtml')
            ->setProduct($product)
            ->setMessageType('category')
            ->toHtml();
        $result .= $block;

        return $result;
    }
}
