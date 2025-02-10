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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Block\Adminhtml\Catalog\Edit\Tab;

use Bss\DynamicCategory\Model\Config\Source\Category;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class ImportRule extends Template
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var string
     */
    protected $_template = 'Bss_DynamicCategory::import.phtml';

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Category $category
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        Category $category,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->category = $category;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Get category
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategory()
    {
        return $this->category->toOptionArray();
    }
}
