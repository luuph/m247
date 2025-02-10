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
 * @copyright  Copyright (c) 2019-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Controller\Cart;

use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class UpdatePost
 *
 * @package Bss\RewardPoint\Controller\Cart
 */
class UpdatePost extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Bss\RewardPoint\Model\ApplyPoint
     */
    protected $applyPoint;

    /**
     * UpdatePost constructor
     *
     * @param Context $context
     * @param CartFactory $cartFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\RewardPoint\Model\ApplyPoint $applyPoint
     */
    public function __construct(
        Context $context,
        CartFactory $cartFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\RewardPoint\Model\ApplyPoint $applyPoint
    ) {
        parent::__construct($context);
        $this->cartFactory = $cartFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->applyPoint = $applyPoint;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $spendPoint = (int) $this->getRequest()->getParam('spend_reward_point');
        $quote = $this->cartFactory->create()->getQuote();
        $response = $this->applyPoint->calculatorApplyPoint($spendPoint, $quote);

        return $resultJson->setData($response);
    }
}
