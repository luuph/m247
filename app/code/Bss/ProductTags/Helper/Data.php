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
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Helper;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\PageCache\Model\Config;

class Data extends AbstractHelper
{
    const ENABLE_META_TAG = '1';

    const DISABLE_META_TAG = '0';
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $config
     * @param TypeListInterface $typeList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $config,
        TypeListInterface $typeList
    ) {
        parent::__construct($context);
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * @param string $key
     * @param string $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig($key, $store = null)
    {
        if ($store == null || $store == '') {
            $store = $this->storeManager->getStore()->getId();
        }
        $store = $this->storeManager->getStore($store);
        $config = $this->scopeConfig->getValue(
            'bss_producttags/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $config;
    }

    /**
     * @return string
     */
    public function getProductsGridUrl()
    {
        return $this->backendUrl->getUrl('protag/tag/products', ['_current' => true]);
    }

    /**
     * Alert messenger clear Cache after action
     */
    public function messengerCache()
    {
        if ($this->config->isEnabled()) {
            $this->typeList->invalidate(
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
            );
        }
    }
}
