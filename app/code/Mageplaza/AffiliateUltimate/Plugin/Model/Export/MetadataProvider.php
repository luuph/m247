<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Plugin\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class MetadataProvider
 * @package Mageplaza\AffiliateUltimate\Plugin\Model\Export
 */
class MetadataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * MetadataProvider constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Ui\Model\Export\MetadataProvider $subject
     * @param callable $proceed
     * @param DocumentInterface $document
     * @param $fields
     * @param $options
     *
     * @return array
     */
    public function aroundGetRowData(
        \Magento\Ui\Model\Export\MetadataProvider $subject,
        callable $proceed,
        DocumentInterface $document,
        $fields,
        $options
    ) {
        $nameSpace = $this->request->getParam('namespace');
        $row = [];
        $result = $proceed($document, $fields, $options);

        if ($nameSpace === 'affiliate_reports_accounts_listing' || $nameSpace === 'affiliate_sales_listing') {
            foreach ($fields as $column) {
                $customAttribute = $document->getCustomAttribute($column);
                if ($customAttribute) {
                    if (isset($options[$column]) && $column !== 'period') {
                        $key = $customAttribute->getValue();
                        if (isset($options[$column][$key])) {
                            $row[] = $options[$column][$key];
                        } else {
                            $row[] = '';
                        }
                    } else {
                        $row[] = $customAttribute->getValue();
                    }
                }
            }
        }

        return !empty($row) ? $row : $result;
    }
}
