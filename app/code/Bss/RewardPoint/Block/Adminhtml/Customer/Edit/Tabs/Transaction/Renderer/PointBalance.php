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
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Renderer;

use Magento\Framework\DataObject;
use Bss\RewardPoint\Model\ResourceModel\TransactionFactory;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Class point balance
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Renderer
 */
class PointBalance extends AbstractRenderer
{
    /**
     * @var TransactionActions
     */
    private $transactionResource;

    /**
     * PointBalance constructor.
     * @param TransactionFactory $transactionResource
     */
    public function __construct(TransactionFactory $transactionResource)
    {
        $this->transactionResource = $transactionResource;
    }

    /**
     * Render
     *
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        $transactionId = $row->getTransactionId();
        $lastestBalance = $this->transactionResource->create()->getPointBalanceForGrid(
            $transactionId
        );
        return $lastestBalance;
    }
}
