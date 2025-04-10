<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Block\Brand\BrandList;

/**
 * Class Index
 * @package Mageplaza\Shopbybrand\Controller\Index
 */
class Index extends Action
{
    /**
     * @type PageFactory
     */
    protected $resultPageFactory;

    /**
     * @type Http
     */
    protected $request;

    /**
     * @type Data
     */
    protected $helper;

    /**
     * @type BrandList
     */
    protected $brandList;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Http $request
     * @param Data $helper
     * @param BrandList $brandList
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Http $request,
        Data $helper,
        BrandList $brandList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request          = $request;
        $this->helper            = $helper;
        $this->brandList         = $brandList;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->request->isAjax()) {
            if ($this->helper->getBrandConfig('brandlist_style') === '0') {
                $pager = $this->brandList->getLayout()->createBlock(\Mageplaza\Shopbybrand\Block\Brand\BrandList::class)
                    ->setTemplate('brand/list/listing.phtml')->toHtml();
                if($this->helper->checkHyvaTheme()) {
                    $pager = $this->brandList->getLayout()->createBlock(\Mageplaza\Shopbybrand\Block\Brand\BrandList::class)
                        ->setTemplate('hyva/brand/list/listing.phtml')->toHtml();
                }
            } else {
                $pager = $this->brandList->getLayout()->createBlock(\Mageplaza\Shopbybrand\Block\Brand\BrandList::class)
                    ->setTemplate('brand/list/alphabet.phtml')->toHtml();
                if($this->helper->checkHyvaTheme()) {
                    $pager = $this->brandList->getLayout()->createBlock(\Mageplaza\Shopbybrand\Block\Brand\BrandList::class)
                        ->setTemplate('hyva/brand/list/alphabet.phtml')->toHtml();
                }
            }

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['data' => $pager]);
            return $resultJson;
        }

        return $this->helper->isEnabled() ? $this->resultPageFactory->create() : $this->_redirect('noroute');
    }
}
