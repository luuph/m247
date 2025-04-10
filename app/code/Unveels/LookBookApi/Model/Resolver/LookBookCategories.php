<?php

namespace Unveels\LookBookApi\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magezon\Core\Helper\Data;

class LookBookCategories implements ResolverInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    
    /**
     * @var Data
     */
    private $coreData;

    /**
     * Constructor.
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Data $coreData
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        Data $coreData
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->coreData = $coreData;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $collection = $this->categoryCollectionFactory->create();

        $categories = [];
        foreach ($collection as $category) {
            $profiles = $this->getProfilesForCategory($category);
            
            $categories[] = [
                'category_id' => (int)$category->getData('category_id'),
                'identifier' => $category->getData('identifier'),
                'title' => $category->getData('title'),
                'is_active' => (int)$category->getData('is_active'),
                'position' => (int)$category->getData('position'),
                'include_in_menu' => (int)$category->getData('include_in_menu'),
                'canonical_url' => $category->getData('canonical_url'),
                'image' => $category->getData('image'),
                'description' => $category->getData('description'),
                'meta_title' => $category->getData('meta_title'),
                'meta_keywords' => $category->getData('meta_keywords'),
                'meta_description' => $category->getData('meta_description'),
                'store_id' => $category->getData('store_id'),
                'profiles' => $profiles
            ];
        }

        return $categories;
    }

    /**
     * Fetch profiles for a given category.
     *
     * @param \Magezon\LookBook\Model\Category $category
     * @return array
     */
    private function getProfilesForCategory($category)
    {
        $profileCollection = $category->getProfileCollection($category);

        $profiles = [];
        foreach ($profileCollection as $profile) {
            $profiles[] = [
                'profile_id' => (int)$profile->getData('profile_id'),
                'name' => $profile->getData('title'),
                'description' => $profile->getData('description'),
                'image' => $profile->getData('image'),
                'identifier' => $profile->getData('identifier'),
                'marker' => $profile->getData('marker'),
                'page_layout' => $profile->getData('page_layout'),
                'try_on_url' => $profile->getData('try_on_url'),
                'store_id' => $profile->getData('store_id')
            ];
        }

        return $profiles;
    }
}
