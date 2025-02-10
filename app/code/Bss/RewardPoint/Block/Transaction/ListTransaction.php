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
namespace Bss\RewardPoint\Block\Transaction;

/**
 * Class List Transaction
 *
 * Bss\RewardPoint\Block\Transaction
 */
class ListTransaction extends \Bss\RewardPoint\Block\RewardPoint
{
    /**
     * Get transactions
     *
     * @return bool|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTransactions()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            return false;
        }

        $page = isset($params['p']) ? $params['p'] : 1;
        $pageSize = isset($params['limit']) ? $params['limit'] : 10;

        $collection = $this->getTransactionCollection();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Prepare layout
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'list.transaction.pager'
            )
            ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
            ->setShowPerPage(true)
            ->setCollection(
                $this->getTransactions()
            );
            $this->setChild('pager', $pager);
            $this->getTransactions()->load();
        }
        return $this;
    }

    /**
     * Get page html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get view url
     *
     * @param int $transactionId
     * @return string
     */
    public function getViewUrl($transactionId)
    {
        return $this->getUrl('rewardpoint/transaction/', ['id' => $transactionId]);
    }
}
