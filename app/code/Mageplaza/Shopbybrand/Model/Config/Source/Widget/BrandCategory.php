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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Config\Source\Widget;

use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class BrandCategory
 * @package Mageplaza\Shopbybrand\Model\Config\Source\Widget
 */
class BrandCategory implements OptionSourceInterface
{
    /**
     * @var CategoryFactory
     */
    protected $brandCategoryFactory;

    /**
     * BrandCategory constructor.
     *
     * @param CategoryFactory $brandCategoryFactory
     */
    public function __construct(
        CategoryFactory $brandCategoryFactory
    ) {
        $this->brandCategoryFactory = $brandCategoryFactory;
    }

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $brandCategoryCollection = $this->brandCategoryFactory->create()->getCollection();
        $data                    = [];
        foreach ($brandCategoryCollection as $category) {
            if ($category->getStatus()) {
                $data[] = [
                    'value' => $category->getData('cat_id'),
                    'label' => $category->getData('name')
                ];
            }
        }

        return $data;
    }
}
