<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\Walletsystem\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * AddAmountToWallet resolver, used for GraphQL request processing.
 */
class AddAmountToWallet implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $_productRepository;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Pool
     */
    private $cacheFrontendPool;

    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Cart $cart
     * @param Product $product
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Cart $cart,
        Product $product,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cart = $cart;
        $this->product = $product;
        $this->_productRepository = $productRepository;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Resolver method for GraphQL
     *
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $responseMessage=[];
            $result ='';
            $sku = 'wk_wallet_amount';
            $product_detail = $this->_productRepository->get($sku);
            $path = 'payment/walletsystem/minimumamounttoadd';
            $value = $args['price'];
            $this->configWriter->delete($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            $this->cacheFunction();
            $params = [
                'product' => $product_detail->getId(),
                'qty'   => $args['qty'],
                'price' => $args['price']
            ];

            $product = $this->product->load($product_detail->getId());
            $this->cart->addProduct($product, $params);
            $this->cart->save();
            $responseMessage['message'] = "Wallet Amount Added Successfully";
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }

    /**
     * Cache Function
     */
    public function cacheFunction()
    {
            $types = ['config','layout','block_html','collections','reflection','db_ddl',
            'eav','config_integration','config_integration_api',
            'full_page','translate','config_webservice'];
        
            foreach ($types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
    }
}
