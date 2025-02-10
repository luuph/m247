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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Controller\Adminhtml\Rule;

use Bss\RewardPoint\Model\RuleFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class New ConditionHtml
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rule
 */
class NewConditionHtml extends \Magento\Backend\App\Action
{

    /**
     * @var \Bss\RewardPoint\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param Context $context
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        \Bss\RewardPoint\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleFactory=$ruleFactory;
        parent::__construct($context);
    }

    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($this->getRequest()->getParam('type') == null) {
            $typeArr=[];
        } else {
            $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        }
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->ruleFactory->create()
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Is allow
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::rule');
    }
}
