<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_Affiliate
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Controller\Adminhtml\Condition;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Affiliate\Model\CampaignFactory;

/**
 * Class NewActionHtml
 *
 * @package Mageplaza\Affiliate\Controller\Adminhtml\Condition
 */
class NewActionHtml extends Action
{
    /**
     * @var CampaignFactory
     */
    protected $modelFactory;

    /**
     * NewActionHtml constructor.
     *
     * @param Context $context
     * @param CampaignFactory $modelFactory
     */
    public function __construct(
        Context $context,
        CampaignFactory $modelFactory
    ) {
        $this->modelFactory = $modelFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id       = $this->getRequest()->getParam('id');
        $typeArr  = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type     = $typeArr[0];
        $popModel = $this->modelFactory->create();

        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($popModel)
            ->setPrefix('actions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        $model->setJsFormObject($this->getRequest()->getParam('form'));
        $html = $model->asHtmlRecursive();
        $this->getResponse()->setBody($html);
    }
}
