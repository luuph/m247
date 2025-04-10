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

/**
 * Class Products block
 */
class Products extends \Magento\Backend\Block\Template
{
    /**
     * Request variable
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * CarouselRepository variable
     *
     * @var \Webkul\MobikulCore\Api\CarouselRepositoryInterface
     */
    protected $carouselRepository;

    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MobikulCore\Api\CarouselRepositoryInterface $carouselRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MobikulCore\Api\CarouselRepositoryInterface $carouselRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->carouselRepository = $carouselRepository;
        parent::__construct($context, $data);
    }

    /**
     * PrepareLayout function
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("carousel/products.phtml");
    }

    /**
     * GetCarouselProductsJson function
     *
     * @return void
     */
    public function getCarouselProductsJson()
    {
        $carouselImages = "";
        $carouselId = $this->request->getParam("id");
        $carousel = $this->carouselRepository->getById($carouselId);
        $carouselImages = $carousel->getProductIds();
        return $carouselImages;
    }
}
