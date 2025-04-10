<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Block\Adminhtml\Category\Edit\Button;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magezon\LookBook\Model\Category;

class Generic implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param Context                $context
     * @param Registry               $registry
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AuthorizationInterface $authorization
    ) {
        $this->context       = $context;
        $this->registry      = $registry;
        $this->authorization = $authorization;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->authorization->isAllowed($resourceId);
    }

    /**
     * Retrive current category instance
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @param  array $params
     * @return array
     */
    public function getButtonAttribute($params = [])
    {
        $attributes = [
            'mage-init' => [
                'Magento_Ui/js/form/button-adapter' => [
                    'actions' => [
                        [
                            'targetName' => 'lookbook_category_form.lookbook_category_form',
                            'actionName' => 'save',
                            'params'     => $params
                        ]
                    ]
                ]
            ]
        ];

        return $attributes;
    }
}
