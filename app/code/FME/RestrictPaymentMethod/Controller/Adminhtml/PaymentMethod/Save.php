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

use FME\RestrictPaymentMethod\Model\PaymentMethod;
use Magento\Backend\App\Action\Context;
use FME\RestrictPaymentMethod\Model\PaymentMethodFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $model;
    protected $methdodFactory;
    protected $resultPageFactory;
    /**
     * @var \FME\RestrictPaymentMethod\Model\PaymentMethodFactory
     */
    var $typeFactory;

    protected $jsonHelper;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \FME\RestrictPaymentMethod\Model\PaymentMethodFactory $typeFactory
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        PageFactory $resultPageFactory,
        PaymentMethod $model,
        PaymentMethodFactory $methdodFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->methdodFactory = $methdodFactory;
        $this->jsonHelper = $jsonHelper;
        $this->model = $model;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $model=$this->methdodFactory->create();
        $resultRedirect = $this->resultPageFactory->create();
        $data = $this->getRequest()->getPostValue();
           if (isset($data['restrictoptions'])) {
                if($data['restrictoptions']=='0'){
                    if(isset($data['region_id']))
                        $data['region_id']=null;
                }
                if($data['restrictoptions']=='1'){
                    if(isset($data['country']))
                        $data['country']=null;
                }
            }
        if ($data) {
            if (empty($data['rule_id'])) {
                $data['rule_id'] = null;
            }
         
            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model=$model->load($id);
                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_session->setPageData($data);
                    $this->_redirect('paymentmethod/*/edit', ['id' => $model->getId()]);
                    return;
                }
            }
            if ($model->getRuleId()) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            $data = $this->prepareData($data);
            $data = $this->prepareTime($data);
            if ($data=='error') {
                $this->messageManager->addErrorMessage("Duplicate Entry of Day In \"Day & Time\" Section");
                    $data = !empty($data) ? $data : [];
                    // $this->_getSession()->setPageData($data);
                    $this->_session->setPageData($data);
                    return $this->_redirect('paymentmethod/*/edit', ['id' => $model->getRuleId()]);
            }
            $this->model->setData($data);
            try {
                $this->model->loadPost($data);
                $this->model->save();
                $this->messageManager->addSuccess(__('Payment Method has been successfully saved.'));
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        '*/paymentmethod/edit',
                        ['id' => $this->model->getId(),
                        '_current' => true]
                    );
                }
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e . 'Something went wrong while saving the Payment Method.'));
            }
            return $this->_redirect('*/paymentmethod/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
        }
        return $this->_redirect('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_RestrictPaymentMethod::paymentmethod');
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        unset($data['rule']);
        if ((isset($data['customers']) && $data['customers']!=='')) {
            $data['customers']=(array)json_decode($data['customers']);
        } else {
            $data['customers']="";
        }
        if (isset($data['apply_catalog_rule']) && !empty($data['apply_catalog_rule'])) {
            $data['apply_catalog_rule']=implode(",", $data['apply_catalog_rule']);
        } else {
            $data['apply_catalog_rule']="";
        }
        if (isset($data['noapply_catalog_rule']) && !empty($data['noapply_catalog_rule'])) {
            $data['noapply_catalog_rule']=implode(",", $data['noapply_catalog_rule']);
        } else {
            $data['noapply_catalog_rule']="";
        }
        if (isset($data['apply_coupon_id']) && !empty($data['apply_coupon_id'])) {
            $data['apply_coupon_id']=implode(",", $data['apply_coupon_id']);
        } else {
            $data['apply_coupon_id']="";
        }
        if (isset($data['noapply_coupon_id']) && !empty($data['noapply_coupon_id'])) {
            $data['noapply_coupon_id']=implode(",", $data['noapply_coupon_id']);
        } else {
            $data['noapply_coupon_id']="";
        }
        return $data;
    }
    protected function prepareTime($data)
    {

        if (isset($data['assign_timing'])) :
            $dayList=$data['assign_timing'];
            for ($i=0; $i<sizeof($dayList); $i++) {
                $day[$i]['day_id']=$dayList[$i]['day'];
                $day[$i]['open_at']=$dayList[$i]['hopen'].$dayList[$i]['mopen'];
                $day[$i]['close_at']=$dayList[$i]['hclose'].$dayList[$i]['mclose'];
                $count=0;
                for ($j=0; $j<sizeof($dayList); $j++) {
                    if ($dayList[$j]['day']==$day[$i]['day_id']) {
                        $count++;
                    }
                }
                // echo $count;

                if ($count>1) {
                    return 'error';
                }
            }
            unset($data['assign_timing']);
            $data['timing']=$day;
        endif;
        return $data;
    }
}
