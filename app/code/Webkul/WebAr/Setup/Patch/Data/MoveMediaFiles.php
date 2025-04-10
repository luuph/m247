<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MoveMediaFiles implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @return void
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->reader = $reader;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $result = $this->processDefaultImages();
    }

    /**
     * Copy Poster and Sky Images to Media
     *
     * @return bool
     */
    private function processDefaultImages()
    {
        $error = false;
        try {
            $this->createDirectories();
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $ds = "/";
            $baseModulePath = $this->reader->getModuleDir('', 'Webkul_WebAr');
            $mediaDetails = [
                "Wk3dModelPosters" => [
                    "view/base/web/images/poster" => [
                        "default_poster.gif"
                    ]
                ],
                "Wk3dModelSkyboxImg" => [
                    "view/base/web/images/skyimage" => [
                        "nature_HDR.hdr"
                    ]
                ]
            ];

            foreach ($mediaDetails as $mediaDirectory => $imageDetails) {
                foreach ($imageDetails as $modulePath => $images) {
                    foreach ($images as $image) {
                        $path = $directory->getAbsolutePath($mediaDirectory);
                        $mediaFilePath = $path.$ds.$image;
                        $moduleFilePath = $baseModulePath.$ds.$modulePath.$ds.$image;

                        if ($this->file->fileExists($mediaFilePath)) {
                            continue;
                        }

                        if (!$this->file->fileExists($moduleFilePath)) {
                            continue;
                        }

                        $this->file->cp($moduleFilePath, $mediaFilePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $error = true;
        }
        return $error;
    }

    /**
     * Create default directories
     *
     * @return void
     */
    private function createDirectories()
    {
        $mediaDirectories = ['Wk3dModelPosters', 'Wk3dModelSkyboxImg'];
        foreach ($mediaDirectories as $mediaDirectory) {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $path = $directory->getAbsolutePath($mediaDirectory);
            if (!$this->file->fileExists($path)) {
                $this->file->mkdir($path, 0777, true);
            }
        }
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
