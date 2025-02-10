<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2023 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Block;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Olegnax\Athlete2\Model\DynamicStyle\EscapeCss;

class SocialIcons extends Template implements BlockInterface {

    protected $escapeCss;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        EscapeCss $escapeCss,
        Json $json,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->escapeCss = $escapeCss;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    public function getCacheKeyInfo($newval = []) {
        return array_merge([
            'OLEGNAX_SOCIALICONS_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue( Context::CONTEXT_GROUP),
            $this->json->serialize($this->getRequest()->getParams()),
            $this->json->serialize($this->getData()),
        ], parent::getCacheKeyInfo(), $newval);
    }

    public function getSocialsId() {
        return 'ox_' . $this->getNameInLayout();
    }

    public function getSocialLinks() {
        $social = ['facebook',
			'facebook_messenger',
			'instagram',
			'twitter',
            'pinterest',
			'skype',
			'tumblr',
			'youtube',
			'amazon',
			'amazon_pay',
			'kickstarter',
			'stripe',
			'paypal',
			'vimeo',
			'vk',
			'foursquare',
			'flickr',
			'linkedin',
			'whatsapp',
			'telegram_plane',
			'snapchat',
			'reddit',
			'discord',
			'slack',
			'tripadvisor',
			'tiktok',
			'business'];
        $socialLink = [];
        $socialOrder = [];
        foreach($social as $_social) {
            $link = $this->getData($_social . '_link');
            if($link) {
                $socialLink[$_social] = $link;
                $order = $this->getData($_social . '_sort');
                if(empty($order)) {
                    $order = 0;
                }
                $socialOrder[$_social] = abs((int)$order);
            }
        }
        asort($socialOrder);
        $result = [];
        foreach($socialOrder as $_social=>$order) {
            $result[$_social] = $socialLink[$_social];
        }
        return $result;
    }
    public function prepareStyle(array $style, string $separatorValue = ': ', string $separatorAttribute = ';') {
        $style = array_filter($style);
        if (empty($style)) {
            return '';
        }
        foreach ($style as $key => &$value) {
            $value = $key . $separatorValue . $value;
        }
        $style = implode($separatorAttribute, $style);

        return $style;
    }
    public function prepareStyleBlock(array $style) {
        $result = [];
        foreach ($style as $selector=> $_style) {
            $result[$selector] = $this->prepareStyle($_style);
        }
        $result = array_filter($result);
        if (!empty($result)) {
            foreach ($result as $selector => $_style) {
                $result[$selector] = $selector .'{' . $_style .'}';
            }
            return $this->renderStyles(implode("\n",$result));
        }
        return '';
    }

    /**
     * Render Inline styles.
     *
     * @param string $styles CSS styles to render.
     * @return string Rendered CSS styles wrapped in style tags.
     */
    public function renderStyles($styles = ''){
        return $this->escapeCss->renderStyles($styles);
    }
}
