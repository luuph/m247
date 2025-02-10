<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Utils;

use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;

class Pdf
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Repository
     */
    private $labelRepository;

    public function __construct(
        Repository $labelRepository,
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        $this->labelRepository = $labelRepository;
    }

    public function combineLabelsPdf(array $labelsContent): \Zend_Pdf
    {
        $outputPdf = new \Zend_Pdf();

        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = \Zend_Pdf::parse($content);

                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }

        return $outputPdf;
    }

    public function createPdfPageFromImageString(string $imageString): \Zend_Pdf_Page
    {
        if (!extension_loaded('gd')) {
            throw new LocalizedException(__('Extension "gd" is missing. Failed to create shipping label.'));
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $image = imagecreatefromstring($imageString);

        if (!$image) {
            throw new LocalizedException(__('Failed to create shipping label. Please try again.'));
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $xSize = imagesx($image);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $ySize = imagesy($image);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imageinterlace($image, false);
        $page = new \Zend_Pdf_Page($xSize, $ySize);

        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $directory->create();

        $tmpFileName = 'amshipping_labels_' . uniqid((string)Random::getRandomNumber()) . time() . '.png';
        $tmpFilePath = $directory->getAbsolutePath($tmpFileName);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imagepng($image, $tmpFilePath);
        $pdfImage = \Zend_Pdf_Image::imageWithPath($tmpFilePath);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        $directory->delete($tmpFileName);

        if (is_resource($image)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            imagedestroy($image);
        }

        return $page;
    }
}
