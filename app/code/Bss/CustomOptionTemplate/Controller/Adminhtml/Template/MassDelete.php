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

class MassDelete extends Delete
{
    /**
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomOptionTemplate::grid';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * MassDelete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory
     * @param \Magento\Catalog\Model\Product\OptionFactory $option
     * @param \Magento\Catalog\Model\Product\Option\ValueFactory $optionValue
     * @param \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption
     * @param \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue
     * @param \Bss\CustomOptionTemplate\Model\Initialization\Helper $helperController
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory,
        \Magento\Catalog\Model\Product\OptionFactory $option,
        \Magento\Catalog\Model\Product\Option\ValueFactory $optionValue,
        \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption,
        \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue,
        \Bss\CustomOptionTemplate\Model\Initialization\Helper $helperController
    ) {
        parent::__construct(
            $context,
            $templateFactory,
            $option,
            $optionValue,
            $bssOption,
            $bssOptionValue,
            $helperController
        );
        $this->filter = $filter;
    }
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $collection = $this->filter->getCollection($this->templateFactory->create()->getCollection());
        foreach ($collection as $item) {
            $this->helperController->deleteBaseOptionProduct($item->getTemplateId());

            //Check & Save data option in product.
            $template = $this->templateFactory->create()->load($item->getTemplateId());
            $productIds = $template->getProductIds() ? explode(',', $template->getProductIds()) : [];
            foreach ($productIds as $id) {
                $this->helperController->saveHasOptionAndRequire($id);
            }
        }
        $collectionSize = $collection->getSize();
        $collection->walk('delete');

        $this->messageManager->addSuccessMessage(
            __('A total of %1 custom option template(s) have been deleted.', $collectionSize)
        );

        return $resultRedirect->setPath('*/*/');
    }
}
