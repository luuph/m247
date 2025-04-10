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
namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;
use Mageplaza\Shopbybrand\Block\Widget\Advanced\BrandList as WidgetBrandList;

/**
 * Class BrandList
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Widget
 */
class BrandList extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * BrandList constructor.
     *
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        RawFactory $resultRawFactory
    ) {
        parent::__construct($context);

        $this->layoutFactory    = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $uniqId = $this->getRequest()->getParam('uniq_id');

        $brandGrid = $layout->createBlock(
            WidgetBrandList::class,
            '',
            ['data' => ['id' => $uniqId]]
        );

        $html      = $brandGrid->toHtml();
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($html);
    }
}
