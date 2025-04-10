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

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class OrderViewAction extends Column
{
    /**
     * UrlBuilder variable
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Construct function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource["data"]["items"] as &$item) {
                if (isset($item["id"])) {
                    $viewUrlPath = $this->getData("config/viewUrlPath") ?: "#";
                    $urlEntityParamName = $this->getData("config/urlEntityParamName") ?: "id";
                    $urlEntityParamKey = $this->getData("config/urlEntityParamKey") ?: $urlEntityParamName;
                    $item[$this->getData("name")] = [
                        "view" => [
                            "href"  => $this->_urlBuilder->getUrl(
                                $viewUrlPath,
                                [$urlEntityParamKey=>$item[$urlEntityParamName]]
                            ),
                            "label" => ($urlEntityParamName == "customer_id") ? $item["customer_name"] : __("View")
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
