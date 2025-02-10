<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Controller\Adminhtml\Shipping;

use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ShippingLabelManagement;
use Magento\Framework\Controller\ResultFactory;

class Generate extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ShippingLabelManagement
     */
    private $shippingLabelManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ShippingLabelManagement $shippingLabelManagement
    ) {
        parent::__construct($context);
        $this->shippingLabelManagement = $shippingLabelManagement;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $response = '';
        $rmaId = (int)$this->getRequest()->getParam('request_id');
        try {
            $this->shippingLabelManagement->createShippingLabel($rmaId, $this->getRequest()->getParam('data'));
        } catch (\Exception $e) {
            $response = ['message' => __($e->getMessage()), 'error' => true];
        }

        return $resultJson->setData($response);
    }
}
