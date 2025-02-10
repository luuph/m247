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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReport
     */
    protected $optionQtyReport;

    /**
     * @var \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReport $optionQtyReport
     * @param StoreManagerInterface $storeManager
     * @param \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig $moduleConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Bss\CustomOptionAbsolutePriceQuantity\Model\OptionQtyReport $optionQtyReport,
        StoreManagerInterface $storeManager,
        \Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig $moduleConfig,
        array $components = [],
        array $data = []
    ) {
        $this->optionQtyReport = $optionQtyReport;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->moduleConfig = $moduleConfig;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $itemCheck = $this->optionQtyReport->getCollection()->addFieldToFilter(
                    'main_table.option_type_id',
                    ['eq' =>$item['option_type_id']]
                )->getLastItem();
                $item[$fieldName . '_src'] = '#';
                $item[$fieldName . '_alt'] = '';
                $item[$fieldName . '_link'] = '';
                $item[$fieldName . '_orig_src'] = '#';
                if ($itemCheck->getImageUrl()) {
                    $item[$fieldName . '_src'] = $this->getMediaUrl() . $itemCheck->getImageUrl();
                    $item[$fieldName . '_orig_src'] = $this->getMediaUrl() . $itemCheck->getImageUrl();
                }
            }
        }
        return $dataSource;
    }

    /**
     * Prepare
     * display column image when module Bss_CustomOptionImage enable
     */
    public function prepare()
    {
        // check install module  Bss_CustomOptionImage
        if ($this->moduleConfig->checkModuleInstall('Bss_CustomOptionImage')
            && $this->moduleConfig->isBssOptionImageEnable()
        ) {
            $config = $this->getData('config');
            $config['visible']= true;
            $this->setData('config', $config);
        }
        parent::prepare();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
