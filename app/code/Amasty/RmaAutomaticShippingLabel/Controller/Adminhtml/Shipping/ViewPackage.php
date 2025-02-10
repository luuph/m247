<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Controller\Adminhtml\Shipping;

use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\Repository;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultFactory;

class ViewPackage extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Repository
     */
    private $repository;
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Repository $repository,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $rmaId = (int)$this->getRequest()->getParam('request_id');
        $result = $this->repository->getByRequestId($rmaId)->getData();
        unset($result['shipping_label']);

        return $resultJson->setData($this->jsonSerializer->serialize($result));
    }
}
