<?php

namespace MagePsycho\RegionCityPro\Model;

use Magento\Framework\Locale\Resolver as MageLocaleResolver;
use Magento\Framework\App\State;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleResolver
{
    /**
     * @var MageLocaleResolver
     */
    private $mageLocaleResolver;
    /**
     * @var State
     */
    private $state;

    public function __construct(
        MageLocaleResolver $mageLocaleResolver,
        State $state
    ) {
        $this->mageLocaleResolver = $mageLocaleResolver;
        $this->state = $state;
    }

    public function getLocale()
    {
        # BUG - When loaded from admin it shows locale from the store view
        if ($this->state->getAreaCode() == 'adminhtml') {
            return \Magento\Framework\AppInterface::DISTRO_LOCALE_CODE;
        }
        return $this->mageLocaleResolver->getLocale();
    }
}
