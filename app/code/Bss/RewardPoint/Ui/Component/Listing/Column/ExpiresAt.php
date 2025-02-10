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

namespace Bss\RewardPoint\Ui\Component\Listing\Column;

use Bss\RewardPoint\Helper\Data as RewardHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customer Name
 * Bss\RewardPoint\Ui\Component\Listing\Column
 */
class ExpiresAt extends Column
{

    /**
     * @var RewardHelper
     */
    protected $rewardHelper;

    /**
     * PointBalance constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RewardHelper $rewardHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        RewardHelper       $rewardHelper,
        array              $components = [],
        array              $data = []
    ) {
        $this->rewardHelper = $rewardHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $createdAt = isset($item['created_at']) ? $item['created_at'] : false;
                $limitDays = isset($item['expires_at']) ? $item['expires_at'] : false;
                $expiredAt = $createdAt && $limitDays
                    ? $this->rewardHelper->convertExpiredDayToDate($limitDays, $createdAt) : '';
                $item[$this->getData('name')] = $expiredAt;
            }
        }

        return $dataSource;
    }
}
