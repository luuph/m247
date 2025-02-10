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
namespace Bss\RewardPoint\Model\Config\Source;

use Magento\Framework\Module\Manager as ModuleManager;

class Websites implements \Magento\Framework\Option\ArrayInterface
{
    public const ALL_WEBSITES = 24000;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Websites constructor.
     * @param ModuleManager $moduleManager
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(
        ModuleManager $moduleManager,
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $this->moduleManager = $moduleManager;
        $this->systemStore = $systemStore;
    }

    /**
     * To array option
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Store')) {
            return [];
        }

        $websites = $this->systemStore->getWebsiteValuesForForm();

        array_unshift($websites, [
                'label' => __('ALL WEBSITES'),
                'value' => self::ALL_WEBSITES,
            ]);

        return $websites;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $value) {
            $result[$value['value']] =  $value['label'];
        }
        return $result;
    }
}
