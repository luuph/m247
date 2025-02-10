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
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\PdfInvoice\Controller\Adminhtml\Template;
use Mageplaza\PdfInvoice\Helper\Data as HelperData;
use Mageplaza\PdfInvoice\Model\TemplateFactory;
use Mageplaza\PdfInvoice\Model\ResourceModel\Column as ColumnResource;
use Mageplaza\PdfInvoice\Model\ColumnFactory;

/**
 * Class Save
 * @package Mageplaza\PdfInvoice\Controller\Adminhtml\Template
 */
class Save extends Template
{
    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ColumnResource
     */
    protected $columnResource;

    /**
     * @var ColumnFactory
     */
    protected $columnFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TemplateFactory $templateFactory
     * @param DateTime $dateTime
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TemplateFactory $templateFactory,
        DateTime $dateTime,
        HelperData $helperData,
        ColumnResource $columnResource,
        ColumnFactory $columnFactory
    ) {
        $this->dateTime       = $dateTime;
        $this->columnResource = $columnResource;
        $this->columnFactory  = $columnFactory;

        parent::__construct($context, $resultPageFactory, $templateFactory, $helperData);
    }

    /**
     * @return void
     * @var PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $templateId = (int)$this->getRequest()->getParam('id');
        if ($data) {
            $template = $this->_initObject();
            if (!$template->getId() && $templateId) {
                $this->messageManager->addErrorMessage(__('This template does not exist.'));
                $this->_redirect('*/*/');
            }

            if ($template->getId()) {
                $data['updated_at'] = $this->dateTime->gmtDate();
            }

            try {
                $template->addData($data)
                    ->save();

                if (isset($data['columns'])) {
                    try {
                        foreach ($data['columns'] as $id => $columnData) {
                            if (gettype($id) !== 'string') {
                                $column = $this->columnFactory->create()->load($id);
                                if (in_array($column['name'], ['Items', 'Qty', 'Price', 'Subtotal']) || isset($columnData['status'])) {
                                    $column->setStatus(1);
                                } else {
                                    $column->setStatus(0);
                                }
                                $column->setPosition($columnData['position']);
                                $this->columnResource->save($column);
                            } else {
                                $column = $this->columnFactory->create();
                                if (in_array($id, ['Items', 'Qty', 'Price', 'Subtotal']) || isset($columnData['status'])) {
                                    $column->setStatus(1);
                                } else {
                                    $column->setStatus(0);
                                }
                                $column->setPosition($columnData['position']);
                                $column->setName($id);
                                $column->setTemplateId($template->getId());
                                $this->columnResource->save($column);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving template.'));
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved the PDF template.'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/template/edit', ['id' => $template->getId(), 'type' => $template->getType()]);

                    return;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving template.'));
            }
        }

        $this->_redirect('*/*/');
    }
}
