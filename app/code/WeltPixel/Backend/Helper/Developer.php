<?php

namespace WeltPixel\Backend\Helper;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Cron\Model\ScheduleFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Developer extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ProductMetadataInterface $productMetadata,
        ScheduleFactory $scheduleFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    )
    {
        parent::__construct($context);
        $this->productMetaData = $productMetadata;
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $datetime;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
    }

    /**
     * @return string
     */
    public function getCurrentServerUser()
    {
        return get_current_user();
    }

    /**
     * @return string
     */
    public function getCurrentServerUserGroup()
    {
        if (function_exists('posix_getegid')) {
            $groupid   = posix_getegid();
            $groupinfo = posix_getgrgid($groupid);

            if (is_array($groupinfo)) {
                return $groupinfo['name'];
            }
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getMagentoEdition()
    {
        return $this->productMetaData->getEdition() . ' ( ' . $this->productMetaData->getVersion() . ' )';
    }

    /**
     * @param int $pageSize
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection mixed
     */
    public function getLatestCronJobs($pageSize)
    {
        $scheduleCollection = $this->scheduleFactory->create()->getCollection();
        $scheduleCollection->setOrder('schedule_id', 'DESC')
            ->setPageSize($pageSize);

        return $scheduleCollection;
    }

    /**
     * @return string
     */
    public function getServerTime()
    {
        return $this->dateTime->gmtDate();
    }

    /**
     * @return array
     */
    public function getModuleLatestVersion($moduleName)
    {
        $latestVersion = __('N/A');
        $curl = curl_init(\WeltPixel\Backend\Block\Adminhtml\ModulesVersion::MODULE_VERSIONS);

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($curl);

        $latestVersions = json_decode($response, true);

        if (isset($latestVersions['modules'])) {
            $latestVersions = $latestVersions['modules'];
        }

        if (isset($latestVersions[$moduleName])) {
            $latestVersion = $latestVersions[$moduleName]['version'];
        }

        return $latestVersion;
    }


    /**
     * @param $moduleName
     * @return string
     */
    public function getComposerVersion($moduleName)
    {
        if (substr($moduleName, -4) == "_Pro") {
            $moduleName = substr($moduleName, 0, -4);
        }

        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            str_replace(["_Free"], '', $moduleName)
        );


        if (!$path) {
            return __('N/A');
        }

        $dirReader = $this->readFactory->create($path);
        $composerJsonData = $dirReader->readFile('composer.json');
        $data = json_decode($composerJsonData, true);
        return $data['version'] ?? __('N/A');
    }
}
