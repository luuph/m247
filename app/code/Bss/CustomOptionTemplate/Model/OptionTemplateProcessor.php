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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model;

class OptionTemplateProcessor
{
    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @param Initialization\Helper $initializationHelper
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\Initialization\Helper $initializationHelper
    ) {
        $this->initializationHelper = $initializationHelper;
    }

    /**
     * Save custom option schedule
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process($data)
    {
        $this->initializationHelper->setProductOptions($data["options_save"]);
        $this->initializationHelper->setOptionsDelete($data["options_delete"]);
        $this->initializationHelper->saveCustomOptionforProduct($data["template_id"]);
        $this->initializationHelper->deleteOptionOldProductAssign($data["product_ids_delete"], $data["template_id"]);
    }
}
