<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Ui\Component\Listing\Grid\Column;

class GridActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface   $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $editUrl
     * @param string             $deleteUrl
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $gridEditUrl = 'paymentmethod/paymentmethod/edit',
        $gridDeleteUrl = 'paymentmethod/paymentmethod/delete'
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_gridEditUrl = $gridEditUrl;
        $this->_gridDeleteUrl = $gridDeleteUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $action) {
                if (isset($action['rule_id'])) {
                    $action[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl($this->_gridEditUrl, ['id' => $action['rule_id']]),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl($this->_gridDeleteUrl, ['id' => $action['rule_id']]),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "'.$action['name'].'"'),
                                'message' => __('Are you sure you want to delete this record?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}

