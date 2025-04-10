<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Productalert;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Stock resolver
 */
class Stock extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController implements ResolverInterface
{
    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductLoader variable
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productLoader;

    /**
     * ProductAlertStock variable
     *
     * @var \Magento\ProductAlert\Model\Stock
     */
    protected $productAlertStock;

    /**
     * Constructor function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\ProductAlert\Model\Stock $productAlertStock
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\ProductAlert\Model\Stock $productAlertStock,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->productAlertStock = $productAlertStock;
        parent::__construct($helper, $request, $jsonHelper);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            if ($this->wholeData) {
                $storeId = $this->wholeData["storeId"] ?? 1;
                $productId = $this->wholeData["productId"] ?? 0;
                $customerToken = $this->wholeData["customerToken"] ?? "";
                $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
                // Checking customer token //////////////////////////////////////
                if (!$customerId && $customerToken != "") {
                    $returnArray["message"] = __(
                        "As customer you are requesting does not exist, so you need to logout."
                    );
                    $returnArray["otherError"] = "customerNotExist";
                    $customerId = 0;
                }
                // End checking customer token //////////////////////////////////
                $environment = $this->emulate->startEnvironmentEmulation($storeId);
                $product = $this->productLoader->create()->load($productId);
                $model = $this->productAlertStock
                    ->setCustomerId($customerId)
                    ->setProductId($product->getId())
                    ->setPrice($product->getFinalPrice())
                    ->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $model->save();
                $returnArray["message"] = __("Alert subscription has been saved.");
                $returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $returnArray;
            } else {
                throw new \BadMethodCallException(__("Invalid Request"));
            }
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }
}
