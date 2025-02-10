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
namespace Bss\RewardPoint\CustomerData;

use Bss\RewardPoint\Helper\InjectModel;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Get Reward Point
 *
 * Bss\RewardPoint\CustomerData
 */
class GetRewardPoint implements \Magento\Customer\CustomerData\SectionSourceInterface
{

    /**
     * @var InjectModel
     */
    protected $injectModel;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * GetRewardPoint constructor.
     *
     * @param InjectModel $injectModel
     * @param SessionFactory $sessionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        InjectModel $injectModel,
        SessionFactory $sessionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->injectModel = $injectModel;
        $this->sessionFactory = $sessionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $customerId = $this->sessionFactory->create()->getCustomer()->getId();
        $storeId = $this->storeManager->getStore()->getWebsiteId();
        $transactionModel = $this->injectModel->createTransactionModel();
        $balance = $transactionModel->loadByCustomer($customerId, $storeId)->getPointBalance();

        return [
            'rewardPoint' => $balance ?: 0
        ];
    }
}
