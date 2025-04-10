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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel;

use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton block
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * GetButtonData function
     *
     * @return void
     */
    public function getButtonData()
    {
        $carouselId = $this->getCarouselId();
        $data = [];
        if ($carouselId) {
            $data = [
                "label" => __("Delete Carousel"),
                "class" => "delete",
                "id" => "carousel-edit-delete-button",
                "on_click" => "",
                "sort_order" => 20,
                "data_attribute" => ["url"=>$this->getDeleteUrl()],
            ];
        }
        return $data;
    }

    /**
     * GetDeleteUrl function
     *
     * @return void
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["id" => $this->getCarouselId()]);
    }
}
