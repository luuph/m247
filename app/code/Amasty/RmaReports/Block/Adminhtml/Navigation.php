<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Navigation extends Template
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'sales' => [
                'title'     => __('Reports'),
                'resource'  => 'Amasty_RmaReports::rma_reports',
                'children'  => [
                    'overview' => [
                        'title' => __('Overview'),
                        'url' => 'amrmarep/report/index',
                        'resource'  => 'Amasty_RmaReports::report_grid'
                    ],
                    'details' => [
                        'title' => __('Reports in details'),
                        'url' => 'amrmarep/report/details',
                        'resource'  => 'Amasty_RmaReports::report_grid'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isUrlActive($url)
    {
        $url = $this->normalizeUrl($url);

        return (false !== strpos($this->getRequest()->getPathInfo(), "/$url/"));
    }

    /**
     * @param $url
     * @return string
     */
    protected function normalizeUrl($url)
    {
        $parts = explode('/', $url);

        while (count($parts) < 3) {
            $parts []= 'index';
        }

        return implode('/', $parts);
    }
}
