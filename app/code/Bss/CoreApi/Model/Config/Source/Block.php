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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CoreApi\Model\Config\Source;

/**
 * Class Block
 * @package Bss\CoreApi\Model\Config\Source
 */
class Block implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * Block constructor.
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $blockCollection = $this->blockFactory->create()->getCollection();
        $result = [];

        foreach ($blockCollection as $blocks) {
            $result[] = [
                'value' => $blocks->getId(),
                'label' => $blocks->getTitle()
            ];
        }

        return $result;
    }
}