<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magetrend\NewsletterMaker\Helper\Variables;

class MteditorManager
{
    /**
     * @var \Magetrend\NewsletterMaker\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Newsletter\Model\TemplateFactory
     */
    public $templateFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    public $filesystem;

    public $file;

    public $transportBuilder;

    /**
     * @var \Magento\Newsletter\Model\Template\Filter
     */
    public $templateFilter;

    public $subscriberFactory;

    public $uploaderFactory;

    public $importManager;

    public $mediaUploaderFactory;

    public $imageAdapterFactory;

    public $mediaConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\NewsletterMaker\Helper\Data $helper,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Newsletter\Model\Queue\TransportBuilder $transportBuilder,
        \Magento\Newsletter\Model\Template\Filter $templateFilter,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magetrend\NewsletterMaker\Model\ImportManager $importManager,
        \Magento\MediaStorage\Model\File\UploaderFactory $mediaUploaderFactory,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
        \Magetrend\NewsletterMaker\Model\Media\Config $mediaConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->mtHelper = $helper;
        $this->registry = $registry;
        $this->templateFactory = $templateFactory;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->transportBuilder = $transportBuilder;
        $this->templateFilter = $templateFilter;
        $this->subscriberFactory = $subscriberFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->importManager = $importManager;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->mediaConfig = $mediaConfig;
        $this->mediaUploaderFactory = $mediaUploaderFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function initTemplate($idFieldName = 'template_id')
    {
        $id = (int)$this->request->getParam($idFieldName);
        $model = $this->templateFactory->create();
        if ($id) {
            $model->load($id);
        }

        if (!$this->registry->registry('newsletter_template')) {
            $this->registry->register('newsletter_template', $model);
        }
        if (!$this->registry->registry('current_newsletter_template')) {
            $this->registry->register('current_newsletter_template', $model);
        }

        return $model;
    }

    public function setEditMode()
    {
        if (!$this->registry->registry('mt_editor_edit_mode')) {
            $this->registry->register('mt_editor_edit_mode', 1);
        }
    }

    public function saveImage()
    {
        $imageName = $this->request->getParam('block_hash');
        $imageSource = $this->request->getParam('image');
        if (empty($imageName) || empty($imageSource)) {
            return false;
        }

        $imageName .='.png';

        $path = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('mt/newslettermaker');
        if (!is_dir($path)) {
            $this->file->mkdir($path, 0775);
        }

        $data = explode(',', $imageSource);
        $this->file->open(['path'=> $path]);
        $this->file->write($imageName, base64_decode($data[ 1 ]), 0666);

        return true;
    }

    public function saveTemplate($template, $head, $body)
    {
        $documentHtml = $this->mergeTemplate($template, $head, $body);
        $documentHtml = $this->removeMediaUrl($documentHtml);
        $template->setTemplateText($documentHtml)
            ->save();
    }

    public function sendTestEmail($email, $template, $source)
    {
        $subscriber = $this->subscriberFactory->create()
            ->loadByEmail($email);

        if (!$subscriber->getId()) {
            $subscriber->setData([
                'subscriber_email' => $email,
                'store_id' => 0
            ]);
        }

        $this->registry->unregister(Variables::REGISTRY_NEWSLETTER_ID);
        $this->registry->register(Variables::REGISTRY_NEWSLETTER_ID, $template->getId());

        $this->transportBuilder->setTemplateData(
            [
                'template_subject' => $template->getTemplateSubject(),
                'template_text' => $source,
                'template_filter' => $this->templateFilter,
                'template_type' => \Magento\Newsletter\Model\Queue::TYPE_HTML,
            ]
        );

        $transport = $this->transportBuilder->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 0]
        )->setTemplateVars(
            ['subscriber' => $subscriber]
        )->setFrom(
            ['name' => '', 'email' => $email]
        )->addTo(
            $email,
            ''
        )->getTransport();

        $transport->sendMessage();
    }

    public function mergeTemplate($template, $head, $body)
    {
        $documentHtml = $template->getTemplateText();
        $docBegin = '<html>';
        if (strpos($documentHtml, '<head>') !== false) {
            $documentHtml = explode('<head>', $documentHtml);
            $docBegin = $documentHtml[0];
        }

        if (strpos($body, '<body') === false) {
            $body = '<body>'.$body.'</body>';
        }

        $mergedDoc = $docBegin.'<head>'.$head.'</head>'.$body.'</html>';
        return $mergedDoc;
    }

    public function removeMediaUrl($html)
    {
        $html = explode('/newsletter/', $html);
        foreach ($html as $key => $partHtml) {
            $partHtml = explode('src="', $partHtml);
            $html[$key] = $partHtml[0].'src="';
        }
        $html = rtrim(implode('newsletter/', $html), 'src="');
        return $html;
    }

    public function uploadTemplate()
    {
        return $this->importManager->uploadTemplate();
    }

    public function duplicateTemplate($templateId, $name, $subject)
    {
        $template = $this->templateFactory->create();
        $template->load($templateId);

        $templateText = $template->getTemplateText();
        $origTemplateText = $template->getOrigTemplateText();
        $template->setTemplateCode($name)
            ->setTemplateSubject($subject)
            ->setId(null)
            ->setIsMtemail(1)
            ->save();

        $this->importManager->duplicateImages($templateId, $template->getId());

        $template->setTemplateText($this->replaceImagePath($templateText, $templateId, $template->getId()))
            ->setOrigTemplateText($this->replaceImagePath($origTemplateText, $templateId, $template->getId()))
            ->save();

        return $template;
    }

    public function replaceImagePath($templateText, $fromTemplateId, $toTemplateId)
    {
        $templateText = str_replace(
            'src="newsletter/'.$fromTemplateId.'/',
            'src="newsletter/'.$toTemplateId.'/',
            $templateText
        );

        return $templateText;
    }

    public function createFromImportedFile($name, $subject)
    {
        $template = $this->templateFactory->create();
        $senderEmail = $this->scopeConfig->getValue(
            'newslettermaker/template_settings/sender_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        $senderName = $this->scopeConfig->getValue(
            'newslettermaker/template_settings/sender_name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        $template->setTemplateCode($name)
            ->setTemplateSubject($subject)
            ->setTemplateType(\Magento\Newsletter\Model\Template::TYPE_HTML)
            ->setTemplateText('TMP')
            ->setOrigTemplateText('TMP')
            ->setTemplateSenderEmail($senderEmail)
            ->setTemplateSenderName($senderName)
            ->setIsMtemail(1)
            ->save();

        $templateText = $this->importManager->processUpload($template);
        $template->setTemplateText($templateText)
            ->setOrigTemplateText($templateText)
            ->save();

        return $template;
    }

    public function uploadImage($templateId)
    {
        $uploader = $this->mediaUploaderFactory->create(['fileId' => 'files']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
        $imageAdapter = $this->imageAdapterFactory->create();
        $uploader->addValidateCallback('email', $imageAdapter, 'validateUploadFile');
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $result = $uploader->save($mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseMediaPath($templateId)));
        $fileUrl = $this->mediaConfig->getMediaUrl($result['file'], $templateId);
        return $fileUrl;
    }
}
