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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Block\ProductTag;

/**
 * Class View
 *
 * @package Bss\ProductTags\Block\ProductTag
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Add breadcumbs
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _addBreadcrumbs()
    {
        $requestName = $this->getRequest()->getParam('tag');
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbs->addCrumb(
            'tag',
            [
                'label' => $requestName,
                'title' => $requestName,
                'link' => ''
            ]
        );
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     *
     * @return \Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareLayout()
    {
        $requestName = $this->getRequest()->getParam('tag');
        $pageTitle = __("Products tagged with: " . "'" . $requestName . "'");
        $this->_addBreadcrumbs();
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     */
    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}
