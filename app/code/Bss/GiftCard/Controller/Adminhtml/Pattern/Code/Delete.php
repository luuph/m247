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
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Controller\Adminhtml\Pattern\Code;

use Bss\GiftCard\Api\PatternRepositoryInterface;
use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\PatternFactory;
use Bss\GiftCard\Model\ResourceModel\Pattern\Code\CollectionFactory as CodeCollection;
use Bss\GiftCard\Model\ResourceModel\Pattern\CollectionFactory as PatternCollection;
use Bss\GiftCard\Model\ResourceModel\Template\CollectionFactory as TemplateCollection;
use Bss\GiftCard\Model\Template\Image\Config as ImageConfig;
use Bss\GiftCard\Model\TemplateFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class delete
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern\Code
 */
class Delete extends AbstractGiftCard
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param TemplateFactory $giftCardTemplate
     * @param PatternFactory $giftCardPattern
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param LoggerInterface $logger
     * @param TemplateRepositoryInterface $templateService
     * @param PatternRepositoryInterface $patternService
     * @param Data $jsonHelper
     * @param FileFactory $fileFactory
     * @param UploaderFactory $fileUploaderFactory
     * @param CodeFactory $codeFactory
     * @param Csv $csvProcessor
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Filter $filter
     * @param TemplateCollection $templatesFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readFactory
     * @param PatternCollection $patternFactory
     * @param ImageConfig $imageConfig
     * @param Filesystem $fileSystem
     * @param AdapterFactory $imageAdapter
     * @param JsonFactory $resultJsonFactory
     * @param CodeCollection $codeCollection
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        TemplateFactory $giftCardTemplate,
        PatternFactory $giftCardPattern,
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        LoggerInterface $logger,
        TemplateRepositoryInterface $templateService,
        PatternRepositoryInterface $patternService,
        Data $jsonHelper,
        FileFactory $fileFactory,
        UploaderFactory $fileUploaderFactory,
        CodeFactory $codeFactory,
        Csv $csvProcessor,
        Registry $registry,
        DateTime $dateTime,
        Filter $filter,
        TemplateCollection $templatesFactory,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory,
        PatternCollection $patternFactory,
        ImageConfig $imageConfig,
        Filesystem $fileSystem,
        AdapterFactory $imageAdapter,
        JsonFactory $resultJsonFactory,
        CodeCollection $codeCollection
    ) {
        $this->redirect = $redirect;
        parent::__construct(
            $giftCardTemplate,
            $giftCardPattern,
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $logger,
            $templateService,
            $patternService,
            $jsonHelper,
            $fileFactory,
            $fileUploaderFactory,
            $codeFactory,
            $csvProcessor,
            $registry,
            $dateTime,
            $filter,
            $templatesFactory,
            $componentRegistrar,
            $readFactory,
            $patternFactory,
            $imageConfig,
            $fileSystem,
            $imageAdapter,
            $resultJsonFactory,
            $codeCollection
        );
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $patternId = $this->getRequest()->getParam('pattern_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $code = $this->codeFactory->create()->load($id);
            if ($code->getCodeId()) {
                $code->delete();
                $this->messageManager->addSuccessMessage(__('Success'));
            } else {
                $this->messageManager->addErrorMessage(__('We can\'t find an gift card code to delete.'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        if ($patternId) {
            return $resultRedirect->setPath('giftcard/pattern/edit', ['id' => $patternId]);
        } else {
            $refererUrl = $this->redirect->getRefererUrl();
            return $resultRedirect->setPath($refererUrl);
        }
    }
}
