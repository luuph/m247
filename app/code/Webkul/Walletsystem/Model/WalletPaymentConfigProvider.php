<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model;

/**
 * Webkul Walletsystem Class
 */
class WalletPaymentConfigProvider implements WalletPaymentConfigProviderInterface
{
    /**
     * @var WalletPaymentConfigProvider[]
     */
    private $configProviders;

    /**
     * Initialize dependencies
     *
     * @param WalletPaymentConfigProvider[] $configProviders
     */
    public function __construct(
        array $configProviders
    ) {
        $this->configProviders = $configProviders;
    }

    /**
     * Get Config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
