<?php

namespace Staempfli\Seo\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Route\Config as Subject;

class RouteConfigPlugin
{
    const MODULE_NAME = 'Staempfli_Seo';

    /**
     * @param Subject $subject
     * @param $result
     * @param $frontName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetModulesByFrontName(Subject $subject, $result, $frontName)
    {
        if ($frontName === 'robots' && in_array(self::MODULE_NAME, $result)) {
            return [self::MODULE_NAME];
        }
        return $result;
    }
}
