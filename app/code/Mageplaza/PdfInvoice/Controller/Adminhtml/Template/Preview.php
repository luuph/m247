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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\PdfInvoice\Helper\Data;
use Mageplaza\PdfInvoice\Helper\PrintProcess as HelperData;
use Mageplaza\PdfInvoice\Model\Template\Processor;
use Mageplaza\PdfInvoice\Model\TemplateFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Preview
 * @package Mageplaza\PdfInvoice\Controller\Adminhtml\Template
 */
class Preview extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var Processor
     */
    protected $templateProcessor;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Preview constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TemplateFactory $templateFactory
     * @param Processor $templateProcessor
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TemplateFactory $templateFactory,
        Processor $templateProcessor,
        HelperData $helperData,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->templateFactory   = $templateFactory;
        $this->templateProcessor = $templateProcessor;
        $this->helperData        = $helperData;
        $this->logger            = $logger;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Preview'));

        try {
            $templateType = $this->getRequest()->getParam('templateType', 'invoice');
            $templateId   = $this->getRequest()->getParam('templateId');

            if ($templateId) {
                $templateHtml = $this->helperData->getTemplateHtml($templateId, $templateType);
                $templateCss  = $this->getTemplateCss($templateId, $templateType);
            } else {
                $templateHtml = $this->getRequest()->getParam('templateHtml');
                $templateCss  = $this->getRequest()->getParam('templateCss', '');
            }

            if (empty(trim($templateHtml))) {
                $this->_getSession()->setPdfInvoiceMessage([
                    'type'    => 'warning',
                    'message' => __('Please insert content to preview!'),
                ]);

                return $resultPage;
            }
            $templateHtml = $templateHtml . '<style>' . $templateCss . '</style>';
            $data         = $this->helperData->getDataProcess($templateType);
            $store        = $data['store'];
            $processor    = $this->templateProcessor->setVariable($data);

            $processor->setTemplateHtml($templateHtml);
            $contentPreview = $processor->processTemplate();
            $this->helperData->exportToPDF('demo.pdf', $contentPreview, $store->getId(), 'I');
        } catch (InputException $e) {
            $this->_getSession()->setPdfInvoiceMessage([
                'type'    => 'error',
                'message' => __($e->getMessage()),
            ]);
            $this->logger->error($e);
        } catch (Exception $e) {
            $this->_getSession()->setPdfInvoiceMessage([
                'type'    => 'error',
                'message' => __('Can\'t preview. Please check HTML and Css in template.'),
            ]);
            $this->logger->error($e);
        }

        return $resultPage;
    }

    /**
     * @param string|int $templateId
     * @param string $type
     *
     * @return string
     * @throws FileSystemException
     */
    public function getTemplateCss($templateId, $type)
    {
        if ($this->helperData->checkDefaultTemplate($templateId)) {
            $templateCss = $this->helperData->getDefaultTemplateCss($type, $templateId);
        }

        $templateModel = $this->templateFactory->create();
        $template      = $templateModel->load($templateId);
        if ($template->getId()) {
            $templateCss = $template->getTemplateStyles();
        }

        return $templateCss;
    }
}
