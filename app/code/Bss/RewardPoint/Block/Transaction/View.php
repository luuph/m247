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
 * Class view
 *
 * Bss\RewardPoint\Block\Transaction
 */
class View extends \Bss\RewardPoint\Block\RewardPoint
{
    /**
     * Get transaction
     *
     * @return bool|\Bss\RewardPoint\Model\Transaction
     */
    public function getTransaction()
    {
        if (!($transactionId = $this->getRequest()->getParam('id'))) {
            return false;
        }

        $transaction = $this->helperInject->createTransactionModel()->load($transactionId);
        return $transaction;
    }
}
