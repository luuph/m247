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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Controller\Index;

use Magento\Framework\App\Action;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Bss\ConfigurableProductWholesale\Model\ConfigurableData;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class RenderTable extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableProductType;

    /**
     * @var AttributeFactory
     */
    private $eavModel;

    /**
     * @var ConfigurableData
     */
    private $configurableData;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param ProductRepository $productRepository
     * @param Configurable $configurableProductType
     * @param AttributeFactory $eavModel
     * @param ConfigurableData $configurableData
     * @param Json $serialize
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        ProductRepository $productRepository,
        Configurable $configurableProductType,
        AttributeFactory $eavModel,
        ConfigurableData $configurableData,
        Json $serialize,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->configurableProductType = $configurableProductType;
        $this->eavModel = $eavModel;
        $this->configurableData = $configurableData;
        $this->serialize = $serialize;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Load product data
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('noroute');
        }

        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getParam('options');
        $options = $this->serialize->unserialize($data);
        $productId = $options['productId'];
        $product = $this->productRepository->getById($productId);
        $childProducts = $this->configurableProductType->getUsedProductCollection($product)
            ->addAttributeToSelect('*');
        if (!empty($options['option'])) {
            foreach ($options['option'] as $option) {
                $attr = explode('_', $option);
                $attributeCode = $this->loadAttributeCode($attr);
                $childProducts->addAttributeToFilter($attributeCode, $attr[1]);
            }
        } else {
            $attributes =  $this->configurableProductType->getConfigurableAttributesAsArray($product);
            if (count($attributes) > 1) {
                $firstAttr = reset($attributes);
                $childProducts->addAttributeToFilter(
                    $firstAttr['attribute_code'],
                    reset($firstAttr['values'])['value_index']
                );
            }
        }

        $mergedIds = $childProducts->getAllIds();
        $jsonChildInfo = $this->configurableData->getJsonChildInfo($product, $mergedIds);
        return $resultJson->setJsonData(
            $jsonChildInfo
        );
    }

    /**
     * Load atribute code
     *
     * @param array $attr
     * @return string
     */
    protected function loadAttributeCode($attr)
    {
        return $this->eavModel->create()->load($attr[0])->getAttributeCode();
    }
}
