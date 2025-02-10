<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\PdfInvoice\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Mageplaza\PdfInvoice\Helper\Data;

/**
 * Class UpdateProductMetaDescription
 *
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateBusinessInformation implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Data
     */
    protected $helperConfig;

    /**
     * PatchInitial constructor.
     * @param Data $helperConfig
     */
    public function __construct(
        Data $helperConfig
    ) {
        $this->helperConfig = $helperConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->helperConfig->setBusinessInformation();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
