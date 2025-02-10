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

namespace Bss\RewardPoint\Block\Adminhtml\SpentReport;

use Bss\RewardPoint\Model\DataCollection;
use Bss\RewardPoint\Model\ResourceModel\Report\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Chart extends Template
{
    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var DataCollection
     */
    private DataCollection $dataCollection;

    /**
     * Create constructor
     *
     * @param Json $json
     * @param DataCollection $dataCollection
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Json             $json,
        DataCollection   $dataCollection,
        Template\Context $context,
        array            $data = []
    ) {
        $this->dataCollection = $dataCollection;
        parent::__construct($context, $data);
        $this->json = $json;
    }

    /**
     * Calculate total
     *
     * @return string|bool
     */
    public function getTotal()
    {
        $result = [
            'total_spent_point' => 0,
            'total_earn_point' => 0
        ];
        /** @var $collection Collection */
        $collection = $this->dataCollection->dataCollection;
        foreach ($collection->getItems() as $interval) {
            if ($interval != "" && $interval->getChildren()) {
                foreach ($interval->getChildren()->getItems() as $item) {
                    $result['total_spent_point'] += $item->getData()['total_spent_point'];
                    $result['total_earn_point'] += $item->getData()['total_earn_point'];
                }
            }
        }
        $total = 0;
        foreach ($result as $item => $value) {
            $total += $value;
        }
        if ($total != 0) {
            foreach ($result as $item => $value) {
                $result[$item] = 100 * $value / $total;
            }
        }
        return $this->json->serialize($result);
    }
}
