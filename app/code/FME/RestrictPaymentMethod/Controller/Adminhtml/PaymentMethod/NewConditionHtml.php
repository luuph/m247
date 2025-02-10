<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Controller\Adminhtml\PaymentMethod;

use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type)->setId($id)->setType($type)
                ->setRule($this->_objectManager->create('FME\RestrictPaymentMethod\Model\PaymentMethod'))
                ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
        protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_RestrictPaymentMethod::paymentmethod');
    }
}
