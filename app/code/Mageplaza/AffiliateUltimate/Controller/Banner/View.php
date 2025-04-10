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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Controller\Banner;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\Affiliate\Helper\Data as DataHelper;
use Mageplaza\AffiliatePro\Model\BannerFactory;
use Mageplaza\AffiliatePro\Model\ResourceModel\Banner as BannerResource;

/**
 * Class View
 * @package Mageplaza\AffiliateUltimate\Controller\Banner
 */
class View extends Action
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BannerResource
     */
    protected $bannerResource;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param BannerFactory $bannerFactory
     * @param StoreManagerInterface $storeManager
     * @param BannerResource $bannerResource
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        RawFactory $resultRawFactory,
        FileFactory $fileFactory,
        BannerFactory $bannerFactory,
        StoreManagerInterface $storeManager,
        BannerResource $bannerResource
    ) {
        $this->dataHelper       = $dataHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory      = $fileFactory;
        $this->bannerFactory    = $bannerFactory;
        $this->storeManager     = $storeManager;
        $this->bannerResource   = $bannerResource;

        parent::__construct($context);
    }

    /**
     * @return Raw
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        $filesystem = $this->_objectManager->get(Filesystem::class);
        $directory  = $filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $token  = $this->getRequest()->getParam('key');
        $banner = $this->bannerFactory->create();

        $this->bannerResource->load($banner, $token, 'token');

        if (!$banner->getId()) {
            return $resultRaw;
        }

        try {
            $img = $banner->getContentHtml();
        } catch (\Exception $e) {
            $img = "";
        }

        preg_match('/src="([^"]*)"/', $img, $matches);

        if (!count($matches)) {
            return $resultRaw;
        }

        $fileName  = $matches[1];
        $mediaPath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $fileName  = str_replace($mediaPath, "", $fileName);
        $path      = $directory->getAbsolutePath($fileName);

        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile($fileName)
                && !$this->_objectManager->get(
                    Storage::class
                )->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'png':
                $contentType = 'image/png';
                break;
            default:
                $contentType = 'application/octet-stream';
                break;
        }

        $stat = $directory->stat($fileName);

        $contentLength = $stat['size'];
        $contentModify = $stat['mtime'];

        $resultRaw->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength)
            ->setHeader('Last-Modified', date('r', $contentModify));
        $resultRaw->setContents($directory->readFile($fileName));

        $impression = $banner->getImpression() + 1;
        $banner->setImpression($impression);

        try {
            $this->bannerResource->save($banner);
        } catch (\Exception $e) {
            return $resultRaw;
        }

        return $resultRaw;
    }
}
