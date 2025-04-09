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
 * @package    Bss_OrderRestriction
 * @author     Extension Team
 * @copyright  Copyright (c) 2021-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\Source;

use Bss\OrderRestriction\Helper\ConfigProvider;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Class OrderRuleConfiguration
 * Get default config value for sale allowed product
 */
class OrderRuleConfiguration implements ValueSourceInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * OrderRuleConfiguration constructor.
     *
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritDoc
     */
    public function getValue($name)
    {
        return $this->configProvider->getDefaultSaleQtyValue();
    }
}
