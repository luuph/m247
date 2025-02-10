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
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model;

use Bss\CustomOptionTemplate\Helper\HelperModelTemplate;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Rule\Model\AbstractModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Template extends AbstractModel
{
    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var HelperModelTemplate
     */
    protected $helperModelTemplate;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * Template constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param HelperModelTemplate $helperModelTemplate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Bss\CustomOptionTemplate\Helper\HelperModelTemplate $helperModelTemplate,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->helperModelTemplate = $helperModelTemplate;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\CustomOptionTemplate\Model\ResourceModel\Template::class);
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function getActionsInstance()
    {
        return $this->helperModelTemplate->getRuleProductFactory()->create();
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->helperModelTemplate->getCombineFactory()->create();
    }

    /**
     * @return string
     */
    public function filterProducts()
    {
        try {
            $productCollection = $this->helperModelTemplate->getProductCollectionFactory()->create();
            $this->setCollectedAttributes([]);
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->helperModelTemplate->getIterator()->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->helperModelTemplate->gettProductModelFactory()->create()
                ]
            );
            if ($this->productIds) {
                return implode(",", $this->productIds);
            }

        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
        }
        return "";
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $isBundleDynamic = false;
        if ($product->getTypeId() =='bundle') {
            $loadProduct = $this->productRepository->getById($product->getId());
            if ($loadProduct->getPriceType() == 0) {
                $isBundleDynamic = true;
            }
        }
        if ($this->getConditions()->validate($product)
            && $product->getTypeId() !=='grouped'
            && !$isBundleDynamic
        ) {
            $this->productIds[] = $product->getId();
        }
    }

    /**
     * @param string $data
     * @param int $templateId
     * @return mixed
     */
    public function setOptionTemplateData($data, $templateId)
    {
        return $this->getResource()->setOptionTemplateData($data, $templateId);
    }
}
