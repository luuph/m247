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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Controller\Adminhtml\Template;

class Delete extends \Magento\Backend\App\Action
{
    /**
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomOptionTemplate::grid';

    /**
     * @var \Bss\CustomOptionTemplate\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $option;

    /**
     * @var \Magento\Catalog\Model\Product\Option\ValueFactory
     */
    protected $optionValue;

    /**
     * @var \Bss\CustomOptionTemplate\Model\OptionFactory
     */
    protected $bssOption;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Option\ValueFactory
     */
    protected $bssOptionValue;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Initialization\Helper
     */
    protected $helperController;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory
     * @param \Magento\Catalog\Model\Product\OptionFactory $option
     * @param \Magento\Catalog\Model\Product\Option\ValueFactory $optionValue
     * @param \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption
     * @param \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue
     * @param \Bss\CustomOptionTemplate\Model\Initialization\Helper $helperController
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory,
        \Magento\Catalog\Model\Product\OptionFactory $option,
        \Magento\Catalog\Model\Product\Option\ValueFactory $optionValue,
        \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption,
        \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue,
        \Bss\CustomOptionTemplate\Model\Initialization\Helper $helperController
    ) {
        parent::__construct($context);
        $this->templateFactory = $templateFactory;
        $this->option = $option;
        $this->optionValue = $optionValue;
        $this->bssOption = $bssOption;
        $this->bssOptionValue = $bssOptionValue;
        $this->helperController = $helperController;
    }

    /**
     * Delete Template
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $templateId = $this->getRequest()->getParam('template_id', null);
        $template = $this->templateFactory->create()->load($templateId);
        if ($template->getId()) {
            try {
                $this->helperController->deleteBaseOptionProduct($templateId);

                //Check & Save data option in product.
                $productIds = $template->getProductIds() ? explode(',', $template->getProductIds()) : [];
                foreach ($productIds as $id) {
                    $this->helperController->saveHasOptionAndRequire($id);
                }

                $template->delete();
                $this->messageManager->addSuccessMessage(
                    __('Delete Custom Option Template successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
