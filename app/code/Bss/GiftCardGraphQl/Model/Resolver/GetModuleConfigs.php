<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetModuleConfigs
 *
 * @package Bss\GiftCardGraphQl\Model\Resolver
 */
class GetModuleConfigs implements ResolverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * GetModuleConfigs constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = [];
        $storeId = $args['store_view'];
        $configs = $this->getConfig($storeId);
        if ($configs) {
            $result['active'] = (int) $configs['general']['active'] ?? null;
            $result['active_to_sender'] = (int) $configs['email']['active_to_sender'] ?? null;
            $result['sender_identity'] =  $configs['email']['identity'] ?? null;
            $result['to_sender'] =  $configs['email']['to_sender'] ?? null;
            $result['to_recipient'] =  $configs['email']['to_recipient'] ?? null;
            $result['notify_to_recipient'] =  $configs['email']['notify_to_recipient'] ?? null;
            $result['day_before_notify_expire'] =  $configs['email']['day_before_notify_expire'] ?? null;
            $result['expire_day'] =  $configs['setting']['expire_day'] ?? null;
            $result['number_character'] =  $configs['setting']['number_character'] ?? null;
            $result['replace_character'] =  $configs['setting']['replace_character'] ?? null;
            $result['max_time_limit'] =  $configs['setting']['max_time_limit'] ?? null;
        }
        return $result;
    }

    /**
     * Get config
     *
     * @param null|int $storeId
     * @return array
     */
    protected function getConfig($storeId = null)
    {
        $scope = $storeId ? ScopeInterface::SCOPE_STORES : 'default';
        return $this->scopeConfig->getValue(
            'giftcard',
            $scope,
            $storeId
        );
    }
}
