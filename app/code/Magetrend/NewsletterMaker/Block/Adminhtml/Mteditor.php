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

namespace Magetrend\NewsletterMaker\Block\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * MT Editor class
 */
class Mteditor extends \Magento\Backend\Block\Template
{

    const MEDIA_IMAGE_DIR = 'newsletter/';

    /**
     * Default font list
     * @var array
     */
    public $defaultFonts = [
        'Arial', 'Arial Black', 'Bookman',  'Comic Sans MS', 'Courier', 'Courier New', 'Georgia', 'Garamond',
        'Helvetica', 'Impact', 'Palatino', 'Times New Roman', 'Times',  'Trebuchet MS', 'Verdana',
    ];

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magetrend\NewsletterMaker\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Template\CollectionFactory
     */
    public $templateCollectionFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * @var \Magetrend\NewsletterMaker\Model\Source\StoreVariables
     */
    public $storeVariables;

    /**
     * @var \Magetrend\NewsletterMaker\Model\Source\Variables
     */
    public $generalVariables;

    /**
     * Mteditor constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magetrend\NewsletterMaker\Helper\Data $moduleHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $read
     * @param \Magento\Newsletter\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magetrend\NewsletterMaker\Model\Source\StoreVariables $storeVariables
     * @param \Magetrend\NewsletterMaker\Model\Source\GeneralVariables $generalVariables
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magetrend\NewsletterMaker\Helper\Data $moduleHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $read,
        \Magento\Newsletter\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magetrend\NewsletterMaker\Model\Source\StoreVariables $storeVariables,
        \Magetrend\NewsletterMaker\Model\Source\GeneralVariables $generalVariables,
        array $data
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->moduleHelper = $moduleHelper;
        $this->readFactory = $read;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->io = $io;
        $this->storeVariables = $storeVariables;
        $this->generalVariables = $generalVariables;
        parent::__construct($context, $data);
    }

    /**
     * Retuns mteditor configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'data' => $this->getTemplateBlockList(),
            'vars' => $this->getVars(),
            'font' => $this->getFonts(),
            'action' => $this->getActions(),
            'formKey' => $this->formKey->getFormKey(),
            'imageList' => $this->getImageList(),
            'template_id' => $this->getTemplateId(),
            'template_html' => $this->getNewsletterTemplate(),
            'document' => $this->getDocumentHtml(),
            'origDocument' => $this->getOrigDocumentHtml(),
            'template' => $this->getTemplateConfig(),
            'iframe' => [
                'css' => [
                    $this->getViewFileUrl('Magetrend_NewsletterMaker::css/mteditor/jquery-ui.css'),
                    $this->getViewFileUrl('Magetrend_NewsletterMaker::css/mteditor/iframe.css'),
                    $this->getViewFileUrl('Magetrend_NewsletterMaker::css/mteditor/images.css')
                ]
            ]
        ];

        return $config;
    }

    /**
     * Returns newsletter config
     *
     * @return array
     */
    public function getTemplateConfig()
    {
        $config = [];
        $template = $this->getNewsletterTemplate();
        if ($template) {
            $config['code'] = $template->getTemplateCode();
            $config['subject'] = $template->getTemplateSubject();
            $config['sender_name'] = $template->getTemplateSenderName();
            $config['sender_email'] = $template->getTemplateSenderEmail();
        }
        return $config;
    }

    /**
     * Returns image list
     *
     * @return array
     */
    public function getImageList()
    {
        $list = [];
        $template = $this->getNewsletterTemplate();
        if (!$template) {
            return $list;
        }

        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::MEDIA_IMAGE_DIR.$template->getId()
        );

        if (!is_dir($path)) {
            $this->io->mkdir($path);
        }

        $baseUrl = $this->getStore()
            ->getBaseUrl('media').self::MEDIA_IMAGE_DIR.$template->getId().'/';
        $fileList = $this->readFactory->create($path)->read();

        if (!empty($fileList)) {
            foreach ($fileList as $fileName) {
                $extension = explode('.', $fileName);
                if (in_array(strtolower(end($extension)), ['jpg', 'png', 'jpeg', 'gif'])) {
                    $list[] = $baseUrl.$fileName;
                }
            }
        }
        return $list;
    }

    /**
     * Returns editor urls
     *
     * @return array
     */
    public function getActions()
    {
        return [
            'back' => $this->getUrl("newsletter/template/index"),
            'saveImage' => $this->getUrl("*/*/saveImage/"),
            'createTemplateUrl' => $this->getUrl("*/*/create/"),
            'uploadUrl' => $this->getUrl("*/*/upload/"),
            'templateUploadUrl' => $this->getUrl("*/*/uploadTemplate/"),
            'saveUrl' => $this->getUrl("*/*/save/"),
            'previewUrl' => $this->getUrl("*/*/preview/"),
            'sendTestEmilUrl' => $this->getUrl("*/*/send/"),
            'saveInfo' => $this->getUrl("*/*/saveInfo/"),
            'deleteTemplateAjax' => $this->getUrl("*/*/delete/"),
        ];
    }

    /**
     * Parse and returns template block list
     *
     * @return array
     */
    public function getTemplateBlockList()
    {
        $template = $this->getNewsletterTemplate();
        if (!$template) {
            return [];
        }

        $parser = $this->moduleHelper->getTemplateParser()->parse($template->getOrigTemplateText());
        $blockList = $parser->getBlockList();

        if (empty($blockList)) {
            return [];
        }

        $blockArray = [];
        $mediaUrl = $this->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mt/newslettermaker');
        $fileList = [];
        if (is_dir($path)) {
            $fileList = $this->readFactory->create($path)->read();
        }

        foreach ($blockList as $key => $block) {
            $block = $this->prepareForEditor($block);
            $blockHash =  md5($block);
            $imageUrl = '';
            if (in_array($blockHash.'.png', $fileList)) {
                $imageUrl = rtrim($mediaUrl, '/').'/mt/newslettermaker/'.$blockHash.'.png';
            }

            $blockName = 'block'.$key;
            $blockArray[$blockName] = [
                'content' => $block,
                'block_hash' => $blockHash,
                'image' => $imageUrl,
                'css' => ''
            ];
        }

        return $blockArray;
    }

    /**
     * Returns available variables for newsletter
     *
     * @param bool $group
     * @return array
     */
    public function getVars($group = false)
    {
        $generalVariables = $this->generalVariables->toOptionArray();
        $storeVars = $this->storeVariables->toOptionArray();

        if (!$group) {
            return array_merge($generalVariables, $storeVars);
        }

        return [
            [
                'label' => __('General'),
                'options' => $generalVariables
            ],
            [
                'label' => __('Store Information'),
                'options' => $storeVars
            ],
        ];
    }

    /**
     * Converting config array to json
     *
     * @return string
     */
    public function getJsonConfig()
    {
        return json_encode($this->getConfig());
    }

    /**
     * Returns newsletter html
     *
     * @return array|string
     */
    public function getDocumentHtml()
    {
        $template = $this->getNewsletterTemplate();
        if (!$template) {
            return '';
        }

        $templateText = $template->getTemplateText();
        return $this->prepareDocumentHtml($templateText);
    }

    /**
     * Returns default newsletter text
     *
     * @return array|string
     */
    public function getOrigDocumentHtml()
    {
        $template = $this->getNewsletterTemplate();
        if (!$template) {
            return '';
        }

        $templateText = $template->getOrigTemplateText();
        return $this->prepareDocumentHtml($templateText);
    }

    /**
     * Add missing tags for documnt
     *
     * @param $templateText
     * @return array|string
     */
    public function prepareDocumentHtml($templateText)
    {
        if (strpos($templateText, '<body') === false) {
            $templateText = '<html><head></head><body>'.$templateText.'</body></html>';
        }

        if (strpos($templateText, '<head') === false) {
            $templateText = explode('<body', $templateText);
            $templateText[0] = $templateText[0].'<head></head>';
            $templateText = implode('<body', $templateText);
        }

        if (strpos($templateText, '<html') === false) {
            $templateText = '<html>'.$templateText.'</html>';
        }

        if (strpos($templateText, '<style') === false) {
            $templateText = explode('</head>', $templateText);
            $templateText[0] = $templateText[0].'<style type="text/css"></style>';
            $templateText = implode('</head>', $templateText);
        }

        $templateText = $this->prepareForEditor($templateText);
        return $templateText;
    }

    /**
     * Prepare template for editor
     *
     * @param $html
     * @return mixed
     */
    public function prepareForEditor($html)
    {
        $html = $this->prepareForEditorImages($html);
        return $html;
    }

    /**
     * Replace images src attributes
     *
     * @param $html
     * @return mixed
     */
    public function prepareForEditorImages($html)
    {
        if ($template = $this->getNewsletterTemplate()) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $html = str_replace(
                'src="newsletter/'.$template->getId().'/',
                'src="'.$mediaUrl.'newsletter/'.$template->getId().'/',
                $html
            );
        }

        return $html;
    }

    /**
     * Returns newsletter template list
     * @return array
     */
    public function getTemplateList()
    {
        $templateList = [];
        $templateCollection = $this->templateCollectionFactory->create();

        if ($templateCollection->getSize() > 0) {
            foreach ($templateCollection as $template) {
                $templateList[] = [
                    'label' => $template->getTemplateCode(),
                    'value' => $template->getId()
                ];
            }
        }

        return $templateList;
    }

    /**
     * Returns current template id
     *
     * @return int
     */
    public function getTemplateId()
    {
        $template = $this->getNewsletterTemplate();
        if (!$template) {
            return 0;
        }
        return $template->getId();
    }

    /**
     * Returns current newsletter template
     *
     * @return bool|mixed
     */
    public function getNewsletterTemplate()
    {
        $template = $this->coreRegistry->registry('current_newsletter_template');
        if (!$template || !$template->getId()) {
            return false;
        }

        return $template;
    }

    /**
     * Returns store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Retunrs available font list
     *
     * @return array
     */
    public function getFonts()
    {
        $fonts = $this->defaultFonts;
        $additionalFont = $this->_scopeConfig->getValue('newslettermaker/editor/fonts');
        if (!empty($additionalFont)) {
            $additionalFont = explode("\n", $additionalFont);
            if (!empty($additionalFont)) {
                $fonts = array_merge($fonts, $additionalFont);
            }
        }

        return $fonts;
    }
}
