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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sitemap extends \Magento\Framework\Model\AbstractModel
{
    public const OPEN_TAG_KEY = 'start';
    public const CLOSE_TAG_KEY = 'end';
    public const TYPE_INDEX = 'sitemap';
    public const TYPE_URL = 'url';

    /**
     * Sitemap items
     *
     * @var array
     */
    public $sitemapItems = [];

    /**
     * Current sitemap increment
     *
     * @var int
     */
    public $sitemapIncrement = 0;

    /**
     * Sitemap start and end tags
     *
     * @var array
     */
    public $tags = [];

    /**
     * Number of lines in sitemap
     *
     * @var int
     */
    public $lineCount = 0;

    /**
     * Current sitemap file size
     *
     * @var int
     */
    public $fileSize = 0;

    /**
     * New line possible symbols
     *
     * @var array
     */
    private $crlf = ["win" => "\r\n", "unix" => "\n", "mac" => "\r"];

    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    public $stream;

    /**
     * Sitemap data
     *
     * @var \Bss\XmlSiteMap\Helper\Data
     */
    public $sitemapData;

    /**
     * @var \Bss\XmlSiteMap\Helper\ProcessSitemap
     */
    private $processSitemap;
    /**
     * @var \Bss\XmlSiteMap\Helper\ProcessVariable
     */
    private $processVariable;

    /**
     * @var int
     */
    protected $countEntityImage = 0;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\XmlSiteMap\Helper\Data $sitemapData
     * @param \Bss\XmlSiteMap\Helper\ProcessSitemap $processSitemap
     * @param \Bss\XmlSiteMap\Helper\ProcessVariable $processVariable
     * @param \Magento\Framework\Escaper $_escaper
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\XmlSiteMap\Helper\Data $sitemapData,
        \Bss\XmlSiteMap\Helper\ProcessSitemap $processSitemap,
        \Bss\XmlSiteMap\Helper\ProcessVariable $processVariable,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->processVariable = $processVariable;
        $this->processSitemap = $processSitemap;
        $this->sitemapData = $sitemapData;
        $this->_escaper = $_escaper;
        $this->driver = $driver;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Bss\XmlSiteMap\Model\ResourceModel\Sitemap::class);
    }

    /**
     * Get file handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('File handler is unreachable'));
        }
    }

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function initSitemapItems()
    {
        $storeId = $this->getStoreId();
        $this->sitemapItems = $this->processSitemap->getSitemapItemCollection(
            $this->sitemapData,
            $storeId
        );
        $urlStyle = $this->getStoreBaseDomain() . '/xmlsitemap/index/style';
        $styleSheet = '<?xml-stylesheet type="text/xsl" href="' . $urlStyle . '"?>';
        $this->tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' . $styleSheet .
                    PHP_EOL .
                    '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' . $styleSheet .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:content="http://www.google.com/schemas/sitemap-content/1.0"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Check sitemap file location and permissions
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $path = $this->getData('xml_sitemap_path');
        $rootPath = $this->sitemapData->getRootPath();
        $rootPath = rtrim($rootPath, '/');
        $path = $rootPath . $path;

        /**
         * Check path is allow
         */
        if ($path && preg_match('#\.\.[\\\/]#', $path)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define a correct path.'));
        }
        /**
         * Check exists and writable path
         */
        if (!$this->processVariable->getDirectory()->isExist($path)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Please create the specified folder "%1" before saving the sitemap.',
                    $this->processVariable->getEscaper()->escapeHtml($this->getData('xml_sitemap_path'))
                )
            );
        }

        if (!$this->processVariable->getDirectory()->isWritable($path)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please make sure that "%1" is writable by the web-server.', $this->getData('xml_sitemap_path'))
            );
        }
        /**
         * Check allow filename
         */
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getData('xml_sitemap_filename'))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.
                    No spaces or other characters are allowed.'
                )
            );
        }
        if (!preg_match('#\.xml$#', $this->getData('xml_sitemap_filename'))) {
            $this->setSitemapFilename($this->getData('xml_sitemap_filename') . '.xml');
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', $this->getBaseDir()), '', $path), '/') . '/');

        return parent::beforeSave();
    }

    /**
     * Generate xml
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function generateXml()
    {
        $this->initSitemapItems();
        /** @var $sitemapItem \Magento\Framework\DataObject */
        foreach ($this->sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();

            foreach ($sitemapItem->getCollection() as $item) {
                $xml = $this->getSitemapRow(
                    $item->getUrl(),
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
                    $item->getImages()
                );

                if ($this->isSplitRequired($xml) && $this->sitemapIncrement > 0) {
                    $this->finalizeSitemap();
                }
                if ($item['check'] == 1) {
                    $this->finalizeSitemap();
                }
                if (!$this->fileSize) {
                    $this->createSitemap();
                }
                $this->writeSitemapRow($xml);
                // Increase counters
                $this->lineCount++;
                $this->fileSize += strlen($xml);
            }
        }

        $this->finalizeSitemap();

        if ($this->sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                $this->getData('xml_sitemap_path'),
                '/'
            ) . '/' . $this->getCurrentSitemapFilename(
                $this->sitemapIncrement
            );

            $rootPath = $this->sitemapData->getRootPath();
            $rootPath = rtrim($rootPath, '/');
            $path = $rootPath . $path;

            $destination = $rootPath . rtrim($this->getData('xml_sitemap_path'), '/')
                . '/' . $this->getData('xml_sitemap_filename');
            $this->processVariable->getDirectory()->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->createSitemapIndex();
        }

        $entityBreakdown = $this->processSitemap->getCountEntity();
        $entityBreakdown['image'] = $this->countEntityImage;
        $entityBreakdownJson = $this->processVariable->jsonEncode($entityBreakdown);
        $this->setEntityBreakdown($entityBreakdownJson);
        $this->setXmlSitemapTime($this->processVariable->getDateModel()->gmtDate('Y-m-d H:i:s'));
        $this->save();
        return $this;
    }

    /**
     * Create sitemap index
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createSitemapIndex()
    {
        $this->createSitemap($this->getData('xml_sitemap_filename'), self::TYPE_INDEX);
        for ($i = 1; $i <= $this->sitemapIncrement; $i++) {
            $xml = $this->getSitemapIndexRow(
                $this->getCurrentSitemapFilename($i),
                $this->processVariable->getCurrentDateTime()
            );
            $this->writeSitemapRow($xml);
        }
        $this->finalizeSitemap(self::TYPE_INDEX);
    }

    /**
     * Check is split required
     *
     * @param string $row
     * @return bool
     */
    public function isSplitRequired($row)
    {
        /** @var $helper \Bss\XmlSiteMap\Helper\Data */
        $helper = $this->sitemapData;
        $storeId = $this->getStoreId();
        if ($this->lineCount + 1 > $helper->getMaximumLinesNumber($storeId)) {
            return true;
        }

        if ($this->fileSize + strlen($row) > $helper->getMaximumFileSize($storeId)) {
            return true;
        }

        return false;
    }

    /**
     * Get sitemap rows
     *
     * @param string $url
     * @param null $lastmod
     * @param null $changefreq
     * @param null $priority
     * @param null $images
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null)
    {
        $url = $this->getUrl($url);

        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($url != null) {
            if ($lastmod) {
                $row .= '<lastmod>' . $this->getFormattedLastmodDate($lastmod) . '</lastmod>';
            }
        }
        if ($changefreq) {
            $row .= '<changefreq>' . $changefreq . '</changefreq>';
        }
        if ($priority) {
            $row .= sprintf('<priority>%.1f</priority>', $priority);
        }
        if ($images) {
            // Add Images to sitemap
            foreach ($images->getCollection() as $image) {
                $this->countEntityImage++;
                $row .= '<image:image>';
                $row .= '<image:loc>' .
                    $this->_escaper->escapeUrl($this->getMediaUrl($image->getUrl()))
                    . '</image:loc>';
                $row .= '<image:title>' . $this->_escaper->escapeHtmlAttr($images->getTitle()) . '</image:title>';
                if ($image->getCaption()) {
                    $row .= '<image:caption>' . $this->_escaper->escapeHtmlAttr($image->getCaption()) . '</image:caption>';
                }
                $row .= '</image:image>';
            }
            // Add PageMap image for Google web search
            $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="' . $this->_escaper->escapeHtmlAttr($images->getTitle()) . '"/>';
            $row .= '<Attribute name="src" value="' . $this->_escaper->escapeHtmlAttr(
                $this->getMediaUrl($images->getThumbnail())
            ) . '"/>';
            $row .= '</DataObject></PageMap>';
        }

        return '<url>' . $row . '</url>';
    }

    /**
     * Get sitemap index row
     *
     * @param string $sitemapFilename
     * @param null $lastmod
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getSitemapIndexRow($sitemapFilename, $lastmod = null)
    {
        $url = $this->getSitemapUrl($this->getData('xml_sitemap_path'), $sitemapFilename);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<sitemap>' . $row . '</sitemap>';
    }

    /**
     * Create new sitemap file
     *
     * @param null|string $fileName
     * @param string $type
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createSitemap($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->sitemapIncrement++;
            $fileName = $this->getCurrentSitemapFilename($this->sitemapIncrement);
        }

        $path = rtrim($this->getData('xml_sitemap_path'), '/') . '/' . $fileName;
        $rootPath = $this->sitemapData->getRootPath();
        $rootPath = rtrim($rootPath, '/');
        $path = $rootPath . $path;

        $this->stream = $this->processVariable->getDirectory()->openFile($path);

        $fileHeader = sprintf($this->tags[$type][self::OPEN_TAG_KEY], $type);
        $this->stream->write($fileHeader);
        $this->fileSize = strlen($fileHeader . sprintf($this->tags[$type][self::CLOSE_TAG_KEY], $type));
    }

    /**
     * White site map rows
     *
     * @param int $row
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function writeSitemapRow($row)
    {
        $this->getStream()->write($row . PHP_EOL);
    }

    /**
     * Finalize site map
     *
     * @param string $type
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function finalizeSitemap($type = self::TYPE_URL)
    {
        if ($this->stream) {
            $this->stream->write(sprintf($this->tags[$type][self::CLOSE_TAG_KEY], $type));
            $this->stream->close();
        }

        // Reset all counters
        $this->lineCount = 0;
        $this->fileSize = 0;
    }

    /**
     * Get current sitemap filename
     *
     * @param int $index
     * @return string
     */
    public function getCurrentSitemapFilename($index)
    {
        $sitemapFilename = $this->getData('xml_sitemap_filename');
        $sitemapFilename = str_replace('.xml', '', $sitemapFilename);
        return $sitemapFilename . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }

    /**
     * Get base dir
     *
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getBaseDir()
    {
        return $this->processVariable->getDirectory()->getAbsolutePath();
    }

    /**
     * Get Store Base Url
     *
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreBaseUrl($type = \Magento\Framework\UrlInterface::URL_TYPE_LINK)
    {
        $store = $this->processVariable->getStoreManager()->getStore($this->getStoreId());
        $isSecure = $store->isUrlSecure();
        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     * Get Url
     *
     * @param string $url
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl($url, $type = \Magento\Framework\UrlInterface::URL_TYPE_LINK)
    {
        if ($url == null) {
            $url = $this->getStoreBaseUrl($type);
            $url = rtrim($url, '/');
            return $url;
        } else {
            if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
                $url = rtrim($url, '/');
                return $url;
            } else {
                return $this->getStoreBaseUrl($type) . ltrim($url, '/');
            }
        }
    }

    /**
     * Get media url
     *
     * @param string $url
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl($url)
    {
        return $this->getUrl($url, \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get date in correct format applicable for lastmod attribute
     *
     * @param string $date
     * @return string
     */
    public function getFormattedLastmodDate($date)
    {
        return date('c', strtotime($date));
    }

    /**
     * Get Document root of Magento instance
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->driver->getRealPath($this->processVariable->getRequest()->getServer('DOCUMENT_ROOT'));
    }

    /**
     * Get store base domain
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function getStoreBaseDomain()
    {
        $url = $this->getStoreBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $storeDomain = rtrim($url, '/');

        return $storeDomain;
    }

    /**
     * Get sitemap Url
     *
     * @param string $sitemapPath
     * @param string $sitemapFileName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getSitemapUrl($sitemapPath, $sitemapFileName)
    {
        return $this->getStoreBaseDomain() . str_replace('//', '/', $sitemapPath . '/' . $sitemapFileName);
    }

    /**
     * Get sitemap Url Raw
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getSiteMapUrlRaw()
    {
        $siteMapPath = $this->getData('xml_sitemap_path');
        $siteMapFileName = $this->getData('xml_sitemap_filename');
        return $this->getStoreBaseDomain() . str_replace('//', '/', $siteMapPath . '/' . $siteMapFileName);
    }

    /**
     * Find new lines delimiter
     *
     * @param string $text
     * @return string
     */
    private function findNewLinesDelimiter($text)
    {
        foreach ($this->crlf as $delimiter) {
            if (strpos($text, $delimiter) !== false) {
                return $delimiter;
            }
        }

        return PHP_EOL;
    }
}
