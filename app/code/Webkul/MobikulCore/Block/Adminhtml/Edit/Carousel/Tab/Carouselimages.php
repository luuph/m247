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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab;

use Magento\Backend\Block\Template\Context;
use \Webkul\MobikulCore\Model\ResourceModel\Carouselimage\CollectionFactory;

/**
 * Class Carouselimages block
 */
class Carouselimages extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * ImagesCollection variable
     *
     * @var CollectionFactory
     */
    protected $imagesCollection;

    /**
     * Construct function parent
     *
     * @param Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $imagesCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $imagesCollection,
        array $data = []
    ) {
        $this->imagesCollection = $imagesCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct function child
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setDefaultDir("ASC");
        $this->setDefaultSort("id");
        $this->setId("carousel_image_grid");
        $this->setSaveParametersInSession(true);
    }

    /**
     * PrepareCollection function
     *
     * @return void
     */
    protected function _prepareCollection()
    {
        $collection = $this->imagesCollection->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * PrepareColumns function
     *
     * @return void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            "triggers",
            [
                "type" => "checkbox",
                "align" => "center",
                "index" => "id"
            ]
        );

        $this->addColumn(
            "id",
            [
                "type" => "number",
                "align" => "center",
                "width" => "30px",
                "index" => "id",
                "header" => __("ID")
            ]
        );

        $this->addColumn(
            "filename",
            [
                "type" => "image",
                "align" => "center",
                "index" => "filename",
                "header" => __("Thumbnail"),
                "escape" => true,
                "filter" => false,
                "renderer" => \Webkul\MobikulCore\Block\Adminhtml\Thumbnail::class,
                "sortable" => false
            ]
        );

        $this->addColumn(
            "title",
            [
                "index" => "title",
                "align" => "left",
                "header" => __("Title")
            ]
        );

        $this->addColumn(
            "status",
            [
                "header" => __("Status"),
                "index" => "status",
                "type" => "options",
                "options" => [1=>__("Enabled"), 0=>__("Disabled")]
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Function to get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl("*/*/imageGridData", ["_current"=>true]);
    }
}
