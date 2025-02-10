<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Controller\Adminhtml\Shipping;

use Amasty\RmaAutomaticShippingLabel\Model\PackagingResolver;
use Magento\Framework\Controller\ResultFactory;

class Packages extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PackagingResolver
     */
    private $packagingResolver;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PackagingResolver $packagingResolver
    ) {
        parent::__construct($context);
        $this->packagingResolver = $packagingResolver;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $rmaId = (int)$this->getRequest()->getParam('request_id');
        $code = $this->getRequest()->getParam('method_code');

        $result = $this->packagingResolver->getPackagingData($rmaId, $code);

        return $resultJson->setData($result);
    }
}
