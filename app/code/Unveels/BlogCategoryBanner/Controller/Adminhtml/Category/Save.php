<?php

namespace Unveels\BlogCategoryBanner\Controller\Adminhtml\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\Helper\Js;
use Mageplaza\Blog\Controller\Adminhtml\Category\Save as BaseSave;
use Mageplaza\Blog\Model\CategoryFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends BaseSave
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param CategoryFactory $categoryFactory
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param Js $jsHelper
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Js $jsHelper,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $categoryFactory,
            $resultRawFactory,
            $resultJsonFactory,
            $layoutFactory,
            $jsHelper
        );
    }

    /**
     * Save action with custom image handling
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/blogCatBan2.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');
        $logger->info(print_r($this->getRequest()->getPost('category'), true));

        if ($data = $this->getRequest()->getPost('category')) {
            try {
                if (isset($_FILES['cat_blog_img']['name']) && $_FILES['cat_blog_img']['name'] !== '') {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'cat_blog_img']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
    
                    $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    $result = $uploader->save($mediaDirectory->getAbsolutePath('blog/category'));

                    if ($result['file']) {
                        $data['cat_blog_img'] = 'blog/category' . $result['file'];
                    }
                }else if(isset($data['cat_blog_img']['value'])) {
                    $imageUrl = $data['cat_blog_img']['value'];
                    $parts = explode('/media/', $imageUrl, 2);
                    $relativePath = isset($parts[1]) ? $parts[1] : $imageUrl;
                    $data['cat_blog_img'] = $relativePath;
                }
                
                $this->getRequest()->setPostValue('category', $data);

                return parent::execute();
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving the image.'));
            }
        }

        return parent::execute();
    }
}
