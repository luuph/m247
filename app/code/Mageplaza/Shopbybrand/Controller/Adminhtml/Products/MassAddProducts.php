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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\Service\BrandUsagePublisher;

/**
 * Class MassAddProducts
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Products
 */
class MassAddProducts extends Gird
{
    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * @var BrandUsagePublisher
     */
    protected $brandUsagePublisher;

    /**
     * MassAddProducts constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param BrandHelper $helper
     * @param BrandUsagePublisher $brandUsagePublisher
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        BrandHelper $helper,
        BrandUsagePublisher $brandUsagePublisher
    ) {
        $this->brandHelper          = $helper;
        $this->brandUsagePublisher = $brandUsagePublisher;
        parent::__construct($context, $resultLayoutFactory, $resultRawFactory);
    }

    /**
     * @return Raw
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRaw         = $this->resultRawFactory->create();
        $optionId          = $this->getRequest()->getParam('option_id');
        $storeId           = $this->getRequest()->getParam('store_id');
        $result['success'] = true;
        if ($productIds = $this->getRequest()->getParam('entity_id')) {
            if (count($productIds) > 1000) {
                $action = BrandUsagePublisher::ADD_BRAND;
                $this->brandUsagePublisher->publish(
                    $this->brandHelper->getAttributeCode($storeId),
                    $storeId,
                    $action,
                    $productIds,
                    $optionId
                );

                return $resultRaw->setContents(AbstractData::jsonEncode($result));
            }
            foreach ($productIds as $productId) {
                try {
                    $this->brandHelper->setBrand($productId, $optionId, $storeId);
                } catch (NoSuchEntityException $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            }
        }
        $this->brandHelper->indexProducts($productIds);

        $qty = $this->brandHelper->collectProductQty($optionId, $this->brandHelper->getAttributeCode($storeId));
        $this->brandHelper->saveQtyOption($optionId, $this->brandHelper->getAttributeCode($storeId), $qty, $storeId);

        return $resultRaw->setContents(AbstractData::jsonEncode($result));
    }
}
