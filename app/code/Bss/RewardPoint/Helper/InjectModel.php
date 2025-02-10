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
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Helper;

use Magento\Framework\App\Helper\Context;
use Bss\RewardPoint\Model\TransactionFactory;
use Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory;
use Bss\RewardPoint\Model\NotificationFactory;
use Bss\RewardPoint\Model\RateFactory;
use Bss\RewardPoint\Model\ResourceModel\TransactionFactory as TransactionResourceFactory;

/**
 * Class data
 *
 * Bss\RewardPoint\Helper
 */
class InjectModel extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollection;

    /**
     * @var \Bss\RewardPoint\Model\NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\TransactionFactory
     */
    protected $transactionResourceFactory;

    /**
     * InjectModel constructor.
     *
     * @param Context $context
     * @param TransactionFactory $transactionFactory
     * @param CollectionFactory $transactionCollection
     * @param NotificationFactory $notificationFactory
     * @param RateFactory $rateFactory
     * @param TransactionResourceFactory $transactionResourceFactory
     */
    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        CollectionFactory $transactionCollection,
        NotificationFactory $notificationFactory,
        RateFactory $rateFactory,
        TransactionResourceFactory $transactionResourceFactory
    ) {
        $this->transactionCollection = $transactionCollection;
        $this->transactionFactory = $transactionFactory;
        $this->notificationFactory = $notificationFactory;
        $this->rateFactory = $rateFactory;
        $this->transactionResourceFactory = $transactionResourceFactory;
        parent::__construct($context);
    }

    /**
     * Create transaction model
     *
     * @return \Bss\RewardPoint\Model\Transaction
     */
    public function createTransactionModel()
    {
        return $this->transactionFactory->create();
    }

    /**
     * Create transaction collection
     *
     * @return \Bss\RewardPoint\Model\ResourceModel\Transaction\Collection
     */
    public function createTransactionCollection()
    {
        return $this->transactionCollection->create();
    }

    /**
     * Create notification model
     *
     * @return \Bss\RewardPoint\Model\Notification
     */
    public function createNotificationModel()
    {
        return $this->notificationFactory->create();
    }

    /**
     * Create rate model
     *
     * @return \Bss\RewardPoint\Model\Rate
     */
    public function createRateModel()
    {
        return $this->rateFactory->create();
    }

    /**
     * Create transaction resource
     *
     * @return \Bss\RewardPoint\Model\ResourceModel\Transaction
     */
    public function createTransactionResource()
    {
        return $this->transactionResourceFactory->create();
    }
}
