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
namespace Bss\RewardPoint\Block\Adminhtml\Order\Totals;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObjectFactory;

/**
 * Class spend point
 *
 * Bss\RewardPoint\Block\Adminhtml\Order\Totals
 */
class SpendPoint extends Template
{
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * SpendPoint constructor.
     *
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get order
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Init totals
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        if ($source->getBaseRwpAmount()) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('spend_point')
                ->setValue(-$source->getRwpAmount())
                ->setBaseValue(-$source->getBaseRwpAmount())
                ->setLabel(__('Spend Point'));
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
