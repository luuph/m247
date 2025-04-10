<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_AIImageSearch
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AIImageSearch\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class Index extends Action
{
    /**
     * Dependency Initilization
     *
     * @param Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        Context $context,
        private \Magento\Framework\Filesystem $filesystem,
        private \Magento\Framework\Filesystem\Io\File $file,
        private \Magento\Framework\Filesystem\Driver\File $fileDriver,
        private \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        private \Magento\Framework\Session\SessionManagerInterface $session,
        private \Magento\Framework\Serialize\SerializerInterface $serializer,
        private \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        private \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct($context);
    }

    /**
     * Set image data in session.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $returnArr = [];
        try {
            $orignalImage = $this->getRequest()->getPost('origimage');
            if (empty($orignalImage)) {
                throw new LocalizedException(__('Orignal image doesn\'t exist.'));
            }
            list($type, $orignalImage) = explode(';', $orignalImage);
            list(, $orignalImage)      = explode(',', $orignalImage);
            $orignalImage = $this->urlDecoder->decode($orignalImage);
            $croppedImage = $this->getRequest()->getPost('cropimage');
            if (empty($croppedImage)) {
                throw new LocalizedException(__('Cropped image doesn\'t exist.'));
            }
            list($type, $croppedImage) = explode(';', $croppedImage);
            list(, $croppedImage)      = explode(',', $croppedImage);
            $croppedImage = $this->urlDecoder->decode($croppedImage);
            $filename = str_replace(' ', '', $this->getRequest()->getPost('name'));
            if (empty($filename)) {
                throw new LocalizedException(__('File name doesn\'t exist.'));
            }
            $nameParts =  explode(".", $filename);
            $validTypes = ['gif', 'jpg', 'png', 'jpeg'];
            if (in_array($nameParts[1], $validTypes)) {
                $this->session->start();
                $imagePath = '';
                $nameParts[0] = $this->dateTime->gmtTimestamp($this->dateTime->gmtDate());
                $nameParts[0] = $nameParts[0] . $this->generateRandomString(5);
                $imagePath = $nameParts[0][0] . '/' . $nameParts[0][1] . '/';
                $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                // Deleting Old Cropped Image
                if (!empty($this->session->getWkAIImageSearchCroppedImage())) {
                    if ($this->fileDriver->isExists($mediapath . $this->session->getWkAIImageSearchCroppedImage())) {
                        $this->fileDriver->deleteFile($mediapath . $this->session->getWkAIImageSearchCroppedImage());
                    }
                }
                // Deleting Old Orignal Image
                if (!empty($this->session->getWkAIImageSearchOrignalImage())) {
                    if ($this->fileDriver->isExists($mediapath . $this->session->getWkAIImageSearchOrignalImage())) {
                        $this->fileDriver->deleteFile($mediapath . $this->session->getWkAIImageSearchOrignalImage());
                    }
                }
                $mediapath = $mediapath . 'AIImageSearch/';
                $this->session->setWkAIImageSearch(true);
                $this->session->setWkAIImageSearchFileName($filename);
                $append = $this->uploadImage($mediapath . 'Cropped/', $imagePath, $nameParts, $croppedImage);
                $this->session->setWkAIImageSearchCroppedImage(
                    'AIImageSearch/Cropped/' . $imagePath . $nameParts[0] . $append . '.' . $nameParts[1]
                );
                $append = $this->uploadImage($mediapath, $imagePath, $nameParts, $orignalImage);
                $this->session->setWkAIImageSearchOrignalImage(
                    'AIImageSearch/' . $imagePath . $nameParts[0] . $append . '.' . $nameParts[1]
                );
                $this->session->setWkAIImageSearchOrignalFileName($nameParts[0] . $append . '.' . $nameParts[1]);
                $returnArr =  $resultJson->setData(['image_name' => $nameParts[0] . $append . '.' . $nameParts[1]]);
            } else {
                $returnArr =  $resultJson->setData(
                    ['error' => __('Image type not valid. Please upload image of type '.$this->encode($validTypes))]
                );
            }
        } catch (\Exception $e) {
            $returnArr = $resultJson->setData(['error' => $e->getMessage()]);
        }

        return $returnArr;
    }

    /**
     * Encodes the given $arr array which is encoded in the array format
     *
     * @param  array $arr
     * @return array
     */
    public function encode($arr = [])
    {
        return $this->serializer->serialize($arr);
    }

    /**
     * Upload Image
     *
     * @param string $mediapath
     * @param string $imagePath
     * @param array $nameParts
     * @param string $image
     * @return string
     */
    protected function uploadImage($mediapath, $imagePath, $nameParts, $image)
    {
        $result = $this->directoryVerifier($mediapath, $imagePath);
        $times = 0;
        $append = '';
        while ($this->fileDriver->isExists(
            $result['path']  . '/' . $nameParts[0] . $append . '.' . $nameParts[1]
        )) {
            $append = '_' . (++$times);
        }
        $this->fileDriver->filePutContents(
            $result['path']  . '/' . $nameParts[0] . $append . '.' . $nameParts[1],
            $image
        );
        return $append;
    }

    /**
     * Verify Directory Existance
     *
     * @param string $directory
     * @param string $path
     * @return array
     */
    protected function directoryVerifier($directory, $path)
    {
        $folders = explode("/", $path);
        $fileName = $folders[count($folders) - 1];
        unset($folders[count($folders) - 1]);
        $folders = implode("/", $folders);
        if (!$this->fileDriver->isExists($directory . '/' . $folders)) {
            $this->file->mkdir($directory . '/' . $folders, 0777, true);
        }
        return [
            'file_name' => $fileName,
            'path' => $directory . $folders,
            'folders' => $folders
        ];
    }

    /**
     * Generate Random String
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length)
    {
        $str = random_bytes($length);
        $str = base64_encode($str);
        $str = str_replace(["+", "/", "="], "", $str);
        $str = substr($str, 0, $length);
        return $str;
    }
}
