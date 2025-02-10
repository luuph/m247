<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) 2017-2018 Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Model\Template;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Mageplaza\PdfInvoice\Helper\Data;
use Mageplaza\PdfInvoice\Helper\PrintProcess;
use Mageplaza\PdfInvoice\Model\Source\Type;
use Mageplaza\PdfInvoice\Model\Template\TransportBuilder as TransportBuilderPdf;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

/**
 * Class SenderBuilder
 * @package Mageplaza\PdfInvoice\Model\Template
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PrintProcess
     */
    protected $printHelper;

    /**
     * @var CoreSession
     */
    protected $_coreSession;

    /**
     * @var TransportBuilderPdf
     */
    protected $transportBuilderPdf;

    /**
     * SenderBuilder constructor.
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     * @param PrintProcess $printHelper
     * @param SessionManagerInterface $sessionManager
     * @param TransportBuilderPdf $transportBuilderPdf
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        Data $helper,
        PrintProcess $printHelper,
        SessionManagerInterface $sessionManager,
        TransportBuilderPdf $transportBuilderPdf
    ) {
        $this->helper       = $helper;
        $this->printHelper  = $printHelper;
        $this->_coreSession = $sessionManager;
        $this->transportBuilderPdf = $transportBuilderPdf;

        parent::__construct($templateContainer, $identityContainer, $transportBuilder);
    }

    /**
     * @inheritdoc
     */
    public function send()
    {
        $attachPdf = $this->attachPDF();
        if ($attachPdf) {
            // attach pdf, override send function
            $this->configureEmailTemplate();
            $this->transportBuilder->addTo(
                $this->identityContainer->getCustomerEmail(),
                $this->identityContainer->getCustomerName()
            );
            $copyTo = $this->identityContainer->getEmailCopyTo();
            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() === 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }
            // transport email
            $this->attachEmail($attachPdf);
        } else {
            parent::send();
        }
    }

    /**
     * @inheritdoc
     */
    public function sendCopyTo()
    {
        $attachPdf = $this->attachPDF();
        if ($attachPdf) {
            $copyTo = $this->identityContainer->getEmailCopyTo();
            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() === 'copy') {
                foreach ($copyTo as $email) {
                    $this->configureEmailTemplate();
                    $this->transportBuilder->addTo($email);
                    $this->attachEmail($attachPdf);
                }
            }
        } else {
            parent::sendCopyTo();
        }
    }

    /**
     * Attach pdf
     *
     * @return bool
     */
    public function attachPDF()
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        $storeId      = $templateVars['store']->getId();
        if ($this->helper->isEnabled($storeId)) {
            try {
                if (isset($templateVars['invoice'])) {
                    $invoice  = $templateVars['invoice'];
                    $content  = $this->printHelper->processPDFTemplate(
                        Type::INVOICE,
                        $templateVars,
                        $storeId,
                        $invoice->getOrder()
                    );
                    if (strpos($this->helper->getFileName('invoice', $storeId), '%increment_id') !== false) {
                        $fileName = str_replace("%increment_id", $invoice->getIncrementId(), $this->helper->getFileName('invoice', $storeId));
                    } else if (!is_null($this->helper->getFileName('invoice', $storeId))) {
                        $fileName = $this->helper->getFileName('invoice', $storeId);
                    } else {
                        $fileName = 'Invoice' . $invoice->getIncrementId();
                    }
                } elseif (isset($templateVars['shipment'])) {
                    $shipment = $templateVars['shipment'];
                    $content  = $this->printHelper->processPDFTemplate(
                        Type::SHIPMENT,
                        $templateVars,
                        $storeId,
                        $shipment->getOrder()
                    );
                    if (strpos($this->helper->getFileName('shipment', $storeId), '%increment_id') !== false) {
                        $fileName = str_replace("%increment_id", $shipment->getIncrementId(), $this->helper->getFileName('shipment', $storeId));
                    } else if (!is_null($this->helper->getFileName('shipment', $storeId))) {
                        $fileName = $this->helper->getFileName('shipment', $storeId);
                    } else {
                        $fileName =  'Shipment' . $shipment->getIncrementId();
                    }
                } elseif (isset($templateVars['creditmemo'])) {
                    $creditmemo = $templateVars['creditmemo'];
                    $content    = $this->printHelper->processPDFTemplate(
                        Type::CREDIT_MEMO,
                        $templateVars,
                        $storeId,
                        $creditmemo->getOrder()
                    );
                    if (strpos($this->helper->getFileName('creditmemo', $storeId), '%increment_id') !== false) {
                        $fileName = str_replace("%increment_id", $creditmemo->getIncrementId(), $this->helper->getFileName('creditmemo', $storeId));
                    } else if (!is_null($this->helper->getFileName('creditmemo', $storeId))) {
                        $fileName = $this->helper->getFileName('creditmemo', $storeId);
                    } else {
                        $fileName = 'Creditmemo' . $creditmemo->getIncrementId();
                    }
                } else {
                    $order    = $templateVars['order'];
                    if (strpos($this->helper->getFileName('order', $storeId), '%increment_id') !== false) {
                        $fileName = str_replace("%increment_id", $order->getIncrementId(), $this->helper->getFileName('order', $storeId));
                    } else if (!is_null($this->helper->getFileName('order', $storeId))) {
                        $fileName = $this->helper->getFileName('order', $storeId);
                    } else {
                        $fileName = 'Order' . $order->getIncrementId();
                    }
                    $content  = $this->printHelper->processPDFTemplate(
                        Type::ORDER,
                        $templateVars,
                        $storeId,
                        $order
                    );
                }
                if ($content) {
                    $attachment = $this->transportBuilderPdf->addAttachment(
                        $content,
                        'application/pdf',
                        Mime::DISPOSITION_ATTACHMENT,
                        Mime::ENCODING_BASE64,
                        $fileName . '.pdf'
                    );

                    return $attachment;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $attachPdf
     *
     * @throws MailException
     * @throws  LocalizedException
     */
    public function attachEmail($attachPdf)
    {
        $transport            = $this->transportBuilder->getTransport();
        $html    = $transport->getMessage();
        $message = Message::fromString($html->getRawMessage());
        $body    = $message->getBody();
        if ($this->helper->versionCompare('2.3.3')) {
            $body = quoted_printable_decode($body);
        }
        $part = new Part($body);
        $part->setCharset('utf-8');
        if ($this->helper->versionCompare('2.3.3')) {
            $part->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
            $part->setDisposition(Mime::DISPOSITION_INLINE);
        }
        $part->setType(Mime::TYPE_HTML);
        $bodyPart = new \Laminas\Mime\Message();
        $bodyPart->setParts([$part, $attachPdf]);
        $html->setBody($bodyPart);

        $transport->sendMessage();
    }
}
