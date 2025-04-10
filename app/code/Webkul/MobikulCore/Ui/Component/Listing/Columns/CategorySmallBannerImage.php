<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Ui\Component\Listing\Columns;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class CategorySmallBannerImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * StoreManager variable
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct function
     *
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * PrepareDataSource function
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            $baseTmpPath = "mobikul/categoryimages/smallbanner/";
            $target = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).$baseTmpPath;
            $fieldName = $this->getData("name");
            foreach ($dataSource["data"]["items"] as &$item) {
                $banner = explode(",", $item["smallbanner"] ?? "");
                $imagesContainer = "";
                $images = [];
                foreach ($banner as $value) {
                    if ($value) {
                        $images[] = $target.$value;
                        $imagesContainer = $imagesContainer."<img class='admin__control-thumbnail' alt='".
                            $target.$value."' src='".$target.$value."'  style='max-width: 100%;'/>";
                    }
                }
                $item[$fieldName] = $imagesContainer;
                $item[$fieldName."_src"] = implode(",", $images);
            }
        }
        return $dataSource;
    }
}
