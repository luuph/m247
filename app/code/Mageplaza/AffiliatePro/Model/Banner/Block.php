<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Model\Banner;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Block
 * @package Mageplaza\AffiliatePro\Model\Banner
 */
class Block implements ArrayInterface
{
    /**
     * @var BlockFactory
     */
    protected $_cms;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * Block constructor.
     *
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->_cms = $blockFactory;
    }

    /**
     * @return array|bool
     */
    public function toOptionArray()
    {
        $cmsBlock = $this->_cms->create();
        $cmsBlockCollection = $cmsBlock->getCollection();
        if (!$this->_options) {
            foreach ($cmsBlockCollection as $item) {
                $this->_options[] = [
                    'label' => $item->getData('title'),
                    'value' => $item->getData('identifier')
                ];
            }
        }

        return $this->_options;
    }
}
