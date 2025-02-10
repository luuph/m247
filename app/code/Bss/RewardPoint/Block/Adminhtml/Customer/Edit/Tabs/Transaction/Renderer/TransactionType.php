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
use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Class transaction type
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Transaction\Renderer
 */
class TransactionType extends AbstractRenderer
{
    /**
     * @var TransactionActions
     */
    private $transactionType;

    /**
     * TransactionType constructor.
     *
     * @param TransactionActions $transactionType
     */
    public function __construct(TransactionActions $transactionType)
    {
        $this->transactionType =$transactionType;
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
        $typeId = $row->getAction();
        $transaction = $this->transactionType->toArray();
        $transactionType = isset($transaction[$typeId]) ? $transaction[$typeId] : "";
        return $transactionType;
    }
}
