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

class Item extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var \Magento\Framework\DataObject\Factory
	 */
    protected $objectFactory;

	/**
	 * @var \Magento\Catalog\Helper\Product\ConfigurationPool
	 */
    protected $configurationPool;

	/**
	 * @var \Magento\Quote\Model\Quote\Item\Processor
	 */
    protected $itemProcessor;

	/**
	 * @var \Magento\Framework\View\LayoutFactory
	 */
    protected $layoutFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\App\Action\Context             $context           
     * @param \Magento\Framework\DataObject\Factory             $objectFactory     
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool 
     * @param \Magento\Quote\Model\Quote\Item\Processor         $itemProcessor     
     * @param \Magento\Framework\View\LayoutFactory             $layoutFactory     
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory    
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        parent::__construct($context);
		$this->objectFactory     = $objectFactory;
		$this->configurationPool = $configurationPool;
		$this->itemProcessor     = $itemProcessor;
		$this->layoutFactory     = $layoutFactory;
		$this->productFactory    = $productFactory;
		$this->pricingHelper     = $pricingHelper;
    }

    public function execute()
    {
        $result['status'] = false;
        $data             = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
				$options = [];
				parse_str($data['options'], $options);
				$product = $this->productFactory->create();
				$product->load($data['product']);
				
				if (isset($options['super_group']) && $options['super_group']) {
					$price = 0;
					$html = '<dl class="item-options">';
					foreach ($options['super_group'] as $productId => $qty) {
						if ($qty) {
							$product1 = $this->productFactory->create();
							$product1->load($productId);
							$buyeRequest = $this->objectFactory->create([
								'product' => $productId,
								'qty' => $qty
							]);
							$item = $this->getItem($product1, $buyeRequest);
							$html .= '<dt>' . $qty . ' x ' . $product1->getName() . '</dt>';
							$html .= '<dd>' . $this->pricingHelper->currency($item->getProduct()->getFinalPrice(), true, false) . '</dd>';
							$price += $item->getProduct()->getFinalPrice() * $qty;
						}
					}
					$html .= '<dl>';
					$result['price']   = $price;
					$result['options'] = $html;
				} else {
					$options         = $this->objectFactory->create($options);
					$item            = $this->getItem($product, $options);
					$result['price'] = $item->getProduct()->getFinalPrice();
					$optionList = $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
					$layout = $this->layoutFactory->create();
					$block  = $layout->createBlock('\Magento\Framework\View\Element\Template');
					$block->setTemplate('Magezon_LookBook::product/options.phtml')->setOptionList($optionList);
					$result['options'] = $block->toHtml();
				}
				$result['status'] = true;
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

	/**
	 * @return \Magento\Quote\Model\Quote\Item
	 */
	public function getItem($product, $buyRequest)
	{
		$candidate = $this->getCandidate($product, $buyRequest);
		$item      = $this->itemProcessor->init($candidate, $buyRequest);
		$item->setOptions($candidate->getCustomOptions());
		$item->setProduct($product);
		return $item;
	}

	public function getCandidate($product, $buyRequest)
	{
		$cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($buyRequest, $product, \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL);

		/**
		 * Error message
		 */
		if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
		    return strval($cartCandidates);
		}

		/**
		 * If prepare process return one object
		 */
		if (!is_array($cartCandidates)) {
		    $cartCandidates = [$cartCandidates];
		}

		return $cartCandidates[0];
	}
}