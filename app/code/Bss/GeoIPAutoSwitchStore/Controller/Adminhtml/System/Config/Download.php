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
 * @package    Bss_GeoIPAutoSwitchStore
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GeoIPAutoSwitchStore\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Download extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Bss\GeoIPAutoSwitchStore\Helper\Config
     */
    protected $geoIpConfig;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    // BSS Commerce GeoIP csv file
    const BSS_GEOIP_LINK = 'https://drive.google.com/uc?export=download&id=1Wcv-q1jYiY21FVb6aRZG_YCG3FAqPih-';

    /**
     * Download constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Bss\GeoIPAutoSwitchStore\Helper\Config $geoIpConfig
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Bss\GeoIPAutoSwitchStore\Helper\Config $geoIpConfig,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->geoIpConfig = $geoIpConfig;
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->io = $io;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        if ($this->geoIpConfig->getUrlCustom()) {
            $urlCustom = $this->geoIpConfig->getUrlCustom();
        } else {
            $urlCustom = "";
        }
        if (preg_match('/\A[\/]{0,}default\/[A-Za-z0-9\_\.\-]{1,}\.(zip){1}\z/', $urlCustom)) {
            // Url is path like default/GeoIPCountryCSV.zip
            // This is invalid url
            $baseMedia = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
            $urlCustom = trim($baseMedia, '/') .
                '/bss_upload/file/' .
                trim($urlCustom, '/');
        }
        $result = $this->resultJsonFactory->create();
        $myResult['status'] = 'False';
        if ($this->getRequest()->isAjax()) {
            try {
                $rootDir = $this->directoryList->getRoot();
                $diskPath = $rootDir . "/var/bss_import";
                $fileName = $rootDir . "/var/bss_import/GeoIPCountryCSV.zip";

                $this->checkDirExits($diskPath);
                $this->io->mkdir($diskPath, 0775);

                $urlMaxMind = self::BSS_GEOIP_LINK;

                if ($urlCustom != '' || $urlCustom != null) {
                    $urlMaxMind = $urlCustom;
                }

                $content = $this->file->fileOpen($urlMaxMind, 'r');

                $this->file->filePutContents($fileName, $content);

                if ($this->file->isExists($fileName)) {
                    $myResult['status'] = 'Done';
                    return $result->setData($myResult);
                } else {
                    return $result->setData($myResult);
                }
            } catch (\Exception $exception) {
                return $result->setData([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]);
            }
        }
    }

    /**
     * @param string $src
     * @return $this
     */
    protected function checkDirExits($src)
    {
        if ($this->file->isExists($src)) {
            if (!empty($this->file->readDirectory($src))) {
                foreach ($this->file->readDirectory($src) as $file) {
                    if (($file != '.') && ($file != '..')) {
                        if ($this->file->isDirectory($file)) {
                            $this->checkDirExits($file);
                        } else {
                            $this->file->deleteFile($file);
                        }
                    }
                }
            }
            $this->file->deleteDirectory($src);
        }
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_GeoIPAutoSwitchStore::update');
    }
}
