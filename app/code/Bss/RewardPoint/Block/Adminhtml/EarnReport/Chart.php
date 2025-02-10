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

namespace Bss\RewardPoint\Block\Adminhtml\EarnReport;

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
     * Construct function
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
        $this->json = $json;
        $this->dataCollection = $dataCollection;
        parent::__construct($context, $data);
    }

    /**
     * Calculate total
     *
     * @return bool|string
     */
    public function getTotal()
    {
        $result = [
            'earn_report_admin_change' => 0,
            'earn_report_registration' => 0,
            'earn_report_birthday' => 0,
            'earn_report_first_review' => 0,
            'earn_report_review' => 0,
            'earn_report_first_order' => 0,
            'earn_report_order' => 0,
            'earn_report_order_refund' => 0,
            'earn_report_import' => 0,
            'earn_report_subscribe' => 0
        ];
        /** @var $collection Collection */
        $collection = $this->dataCollection->dataCollection;
        foreach ($collection->getItems() as $interval) {
            if ($interval->getChildren()) {
                foreach ($interval->getChildren()->getItems() as $item) {
                    $result['earn_report_admin_change'] += $item['earn_report_admin_change'];
                    $result['earn_report_registration'] += $item['earn_report_registration'];
                    $result['earn_report_birthday'] += $item['earn_report_birthday'];
                    $result['earn_report_first_review'] += $item['earn_report_first_review'];
                    $result['earn_report_review'] += $item['earn_report_review'];
                    $result['earn_report_first_order'] += $item['earn_report_first_order'];
                    $result['earn_report_order'] += $item['earn_report_order'];
                    $result['earn_report_order_refund'] += $item['earn_report_order_refund'];
                    $result['earn_report_import'] += $item['earn_report_import'];
                    $result['earn_report_subscribe'] += $item['earn_report_subscribe'];
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
