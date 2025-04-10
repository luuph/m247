<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Controller\Index;

use \Magento\Framework\Exception\LocalizedException;

class AddToCart extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var \Magento\Checkout\Model\Cart
	 */
	protected $cart;

	/**
	 * @var \Magento\Catalog\Api\ProductRepositoryInterface
	 */
	protected $productRepository;

	/**
	 * @param \Magento\Framework\App\Action\Context           $context           
	 * @param \Magento\Checkout\Model\Cart                    $cart              
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository 
	 */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
		$this->cart              = $cart;
		$this->productRepository = $productRepository;
    }

    public function execute()
    {
    	$result['status'] = false;
    	$data             = $this->getRequest()->getPostValue();
		$storeId          = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId();

        if ($data && isset($data['items'])) {
            try {
            	$valid = true;
				foreach ($data['items'] as $item) {
					if (!isset($item['product'])) {
						if (isset($item['name']) && $item['name']) {
							$this->messageManager->addError(__('Product %1 does not exist. Please remove and add it again.', $item['name']));
						} else {
							$this->messageManager->addError(__('Something went wrong while processing the request.'));
						}
						$valid = false;
						break;
					}
					$product = $this->productRepository->getById($item['product'], false, $storeId);
					$params  = [];
					if (isset($item['options'])) {
						parse_str($item['options'], $params);
					}
					$params['qty'] = $item['qty'];
					try {
						$this->cart->addProduct($product, $params);
					} catch (LocalizedException $e) {
						$valid = false;
						$this->messageManager->addError($e->getMessage());
						$this->messageManager->addError(__('Something went wrong when adding %1 to cart. Please check it again.', $product->getName()));
					} catch (\Exception $e) {
						$valid = false;
						$this->messageManager->addError(__('Something went wrong while processing the request.'));
					}
				}
				if ($valid) {
					$this->cart->save();
					$result['status']      = true;
					$this->messageManager->addSuccess('You added to your '.'<a href="'.$this->_url->getUrl('checkout/cart').'">shopping cart.</a>');
				}
            } catch (LocalizedException $e) {
                $result['message'] = $e->getMessage();
            } catch (\Exception $e) {
            	$result['message'] = __('Something went wrong while processing the request.');
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
        return;
    }
}