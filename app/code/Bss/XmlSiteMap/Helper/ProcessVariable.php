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
namespace Bss\XmlSiteMap\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProcessSitemap
 * @package Bss\XmlSiteMap\Helper
 */
class ProcessVariable
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;
    /**
     * @var \Magento\Framework\Escaper
     */
    public $escaper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateModel;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * ProcessVariable constructor.
     * @param Escaper $escaper
     * @param DateTime $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param TimezoneInterface $timezone
     * @param Json $jsonHelper
     * @param Filesystem $filesystem
     * @param Data $data
     * @throws FileSystemException
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Bss\XmlSiteMap\Helper\Data $data
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->timezone = $timezone;
        $this->escaper = $escaper;
        $this->dateModel = $modelDate;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->data = $data;
    }

    /**
     * Get directory
     *
     * @return Filesystem\Directory\WriteInterface
     * @throws FileSystemException
     */
    public function getDirectory()
    {
        $configPath = $this->data->getRootPath();
        if ($this->data->isMoreThanM242() && substr($configPath, 0, 5) !== "/pub/") {
            $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
        }
        return $this->directory;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getDateModel()
    {
        return $this->dateModel;
    }

    /**
     * @param array $data
     * @return string
     */
    public function jsonEncode(array $data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
    /**
     * Get current date time
     *
     * @return string
     */
    public function getCurrentDateTime()
    {
        return ($this->timezone->date()->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
    }
}
