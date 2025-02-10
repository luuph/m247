<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Controller\Adminhtml\Shipping;

use Amasty\RmaAutomaticShippingLabel\Model\CarriersAndMethodsProvider;
use Magento\Framework\Controller\ResultFactory;

class Carriers extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;
    /**
     * @var CarriersAndMethodsProvider
     */
    private $carriersAndMethodsProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        CarriersAndMethodsProvider $carriersAndMethodsProvider
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->carriersAndMethodsProvider = $carriersAndMethodsProvider;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $response = $this->carriersAndMethodsProvider
            ->getCarriersAndMethodsWithShippingLabels($this->getRequest()->getParam('request_id'));

        return $resultJson->setData($response);
    }
}
