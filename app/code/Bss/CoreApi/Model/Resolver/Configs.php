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
 * @package    Bss_CoreApi
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\CoreApi\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class Configs implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    private $blockRepository;

    /**
     * Configs constructor.
     * @param ValueFactory $valueFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ValueFactory $valueFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockRepository $blockRepository
    )
    {
        $this->valueFactory = $valueFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        $configs = [
            'popup_expire_time' => $this->scopeConfig->getValue(
                'coreapi/popup/expire_time'
            ),
            'popup_delay_open_time' => $this->scopeConfig->getValue(
                'coreapi/popup/delay_open_time'
            ),
            'theme_header_block' => $this->getBlockContent($this->scopeConfig->getValue(
                'coreapi/theme/header_block'
            )),
            'theme_popup_block' => $this->getBlockContent($this->scopeConfig->getValue(
                'coreapi/theme/popup_block'
            )),
        ];

        return $configs;
    }

    /**
     * @param $blockId
     * @return null
     */
    protected function getBlockContent($blockId)
    {
        try {
            return $this->blockRepository->getById($blockId)->getContent();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return null;
        }
    }
}