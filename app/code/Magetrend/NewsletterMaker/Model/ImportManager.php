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

class ImportManager
{
    const TMP_DIR = 'newslettermaker/';

    const NEW_TEMPLATE_DIR = 'new_template/';

    const TEMPLATE_IMAGES_DIR = 'images/';

    public $uploaderFactory;

    public $filesystem;

    public $zip;

    public $io;

    public $readFactory;

    public $read;

    public function __construct(
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\Archive\Zip $zip,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->zip = $zip;
        $this->io = $io;
        $this->readFactory = $readFactory;
    }

    public function uploadTemplate()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $directory->create();
        $directory->delete(self::TMP_DIR);
        $directory->create(self::TMP_DIR);
        $directory->create(self::TMP_DIR.self::NEW_TEMPLATE_DIR);
        $templateDirectory = $directory->getAbsolutePath(self::TMP_DIR.self::NEW_TEMPLATE_DIR);
        $fileUploadDirectory = $directory->getAbsolutePath(self::TMP_DIR);

        $uploader = $this->uploaderFactory->create(['fileId' => 'files']);
        $uploader->setAllowedExtensions(['html', 'zip']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($fileUploadDirectory);

        if (isset($result['name']) && strpos($result['name'], '.zip') !== false) {
            $this->extractArchive($result['path'], $result['file']);
        } elseif ($result['type'] == 'text/html') {
            $this->io->mv(
                $result['path'] . $result['file'],
                $templateDirectory . $result['file']
            );
        }

        $this->validateTemplate($templateDirectory);
        return $result;
    }

    public function extractArchive($filePath, $filName)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $newTemplatePath = self::TMP_DIR.self::NEW_TEMPLATE_DIR;
        $extractDest = $directory->getAbsolutePath($newTemplatePath);
        $zip = new \ZipArchive();
        $zip->open($filePath . $filName);
        $zip->extractTo($extractDest);
        $zip->close();

        $fileList = $this->getRecursivelyFileList($extractDest);
        if (empty($fileList)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to extract archive or archive is empty')
            );
        }
    }

    public function validateTemplate($path)
    {
        $htmlFileList = $this->getRecursivelyFileList($path, ['html']);
        if (empty($htmlFileList)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The archive does not contain .html file'));
        }

        if (!$this->getValidHtmlFile($htmlFileList)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to find newsletter .html file.'));
        }

        return true;
    }

    public function getValidHtmlFile($htmlFileList)
    {
        if (empty($htmlFileList)) {
            return false;
        }

        foreach ($htmlFileList as $key => $file) {
            $fileContent = $this->readFactory->create($file['path'])->readFile($file['file_name']);
            if (strpos($fileContent, '<html') !== false || strpos($fileContent, '<body') !== false) {
                return $file;
            }
        }

        return false;
    }

    public function isHtmlFileValid($fileContent)
    {
        if (strpos($fileContent, '<html') !== false || strpos($fileContent, '<body') !== false) {
            return true;
        }

        return false;
    }

    public function getRecursivelyFileList($destinationPath, $fileTypes = [], $deep = 1)
    {
        if (!is_dir($destinationPath)) {
            return;
        }
        $fileList = [];
        $list = $this->readDirectory($destinationPath);

        foreach ($list as $item) {
            $path = $destinationPath.$item;
            if (is_dir($path)) {
                $fileList = array_merge($this->getRecursivelyFileList($path.'/', $fileTypes, $deep+1), $fileList);
            } else {
                $ext = explode('.', $item);
                if (!empty($fileTypes) && !in_array(end($ext), $fileTypes)) {
                    continue;
                }

                $fileList[] = [
                    'deep' => $deep,
                    'file_name' => $item,
                    'path' => $destinationPath,
                    'full_path' => $path
                ];
            }
        }

        if ($deep == 1) {
            usort($fileList, '\Magetrend\NewsletterMaker\Model\ImportManager::sortByDeep');
        }
        return $fileList;
    }

    public function readDirectory($dir)
    {
        $list = scandir($dir);
        if (empty($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $key => $value) {
            if (in_array($value, ['.', '..', '__MACOSX'])) {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    public static function sortByDeep($a, $b)
    {
        if ($a['deep'] == $b['deep']) {
            return 0;
        }

        return ($a['deep'] < $b['deep'])?-1:1;
    }

    public function processUpload($template)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        if (!$directory->isDirectory(self::TMP_DIR.self::NEW_TEMPLATE_DIR)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to process uploaded template. Please try again')
            );
        }

        $templateDirectory = $directory->getAbsolutePath(self::TMP_DIR.self::NEW_TEMPLATE_DIR);
        $htmlFileList = $this->getRecursivelyFileList($templateDirectory, ['html']);

        if (empty($htmlFileList)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to find uploaded template file.'));
        }

        $fileTypes = [
            'mt' => 0,
            'mailchimp' => 0,
            'default' => 0
        ];

        foreach ($htmlFileList as $file) {
            $fileContent = $this->readFactory->create($file['path'])->readFile($file['file_name']);
            if (!$this->isHtmlFileValid($fileContent)) {
                continue;
            }

            if ($this->isMTTemplate($fileContent)) {
                $fileTypes['mt'] = $file;
            } elseif ($this->isMailchimpTemplate($fileContent)) {
                $fileTypes['mailchimp'] = $file;
            } else {
                $fileTypes['default'] = $file;
            }
        }

        foreach ($fileTypes as $key => $file) {
            if ($file == 0) {
                continue;
            }

            $fileContent = $this->readFactory->create($file['path'])->readFile($file['file_name']);
            if ($key == 'mailchimp') {
                $fileContent = $this->convertTemplateFromMailchimp($fileContent);
            }

            $this->processUploadedImages($template, rtrim($file['path'], '/').'/images/');
            $fileContent = $this->replaceImageLink($template, $fileContent);

            return $fileContent;
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('Unable to find .html template. Please re-upload the file and try again')
        );
    }

    public function isMTTemplate($fileContent)
    {
        if (strpos($fileContent, ' data-repeatable') === false) {
            return false;
        }

        return true;
    }

    public function isMailchimpTemplate($fileContent)
    {
        if (strpos($fileContent, ' mc:repeatable') === false) {
            return false;
        }

        return true;
    }

    public function convertTemplateFromMailchimp($content)
    {
        $content = str_replace(' mc:', ' mt:', $content);

        return $content;
    }

    public function replaceImageLink($template, $fileContent)
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $mediaDirectory->getRelativePath('newsletter/'.$template->getId().'/');
        $fileContent = str_replace('"images/', '"'.$path, $fileContent);
        return $fileContent;
    }

    public function processUploadedImages($template, $imageFilePath)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $relativePath = str_replace($directory->getAbsolutePath('/'), '', $imageFilePath);

        if (!$directory->isExist($relativePath)) {
            return false;
        }

        $emailImagesPath = 'newsletter/'.$template->getId().'/';
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create($emailImagesPath);

        $from =  $directory->getAbsolutePath($relativePath);
        $to = $mediaDirectory->getAbsolutePath($emailImagesPath);

        $fileList = $this->readFactory->create($from)->read();
        if (empty($fileList)) {
            return;
        }

        foreach ($fileList as $file) {
            $this->io->cp($from.$file, $to.$file);
        }
    }

    public function duplicateImages($fromTemplateId, $toTemplateId)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $from = $directory->getAbsolutePath('newsletter/'.$fromTemplateId.'/');
        $to = $directory->getAbsolutePath('newsletter/'.$toTemplateId.'/');
        $this->io->mkdir($to);

        $fileList = $this->readFactory->create($from)->read();
        if (empty($fileList)) {
            return;
        }

        foreach ($fileList as $file) {
            $this->io->cp($from.$file, $to.$file);
        }
    }
}
