<?php

namespace Unveels\BlogCategoryBanner\Block\Adminhtml\Category\Edit\Tab;

use Mageplaza\Blog\Block\Adminhtml\Category\Edit\Tab\Category as BaseCategory;
use Magento\Framework\UrlInterface;

/**
 * Class Category
 */
class Category extends BaseCategory
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');

        // Add your custom field for the category image
        $fieldset->addField('cat_blog_img', 'image', [
            'name'     => 'cat_blog_img',
            'label'    => __('Category Blog Image'),
            'title'    => __('Category Blog Image'),
            'required' => false,
            'note'     => __('Upload an image for the category.'),
        ]);

        $categoryData = $this->_coreRegistry->registry('category');
        if ($categoryData && $categoryData->getId()) {
            $image = $categoryData->getData('cat_blog_img');
            if ($image) {
                $imageUrl = $this->getUrlBuilder()->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $image;
                $categoryData->setData('cat_blog_img', $imageUrl);
            }

            $form->setValues($categoryData->getData());
        }

        return $this;
    }

    /**
     * Get URL builder
     *
     * @return UrlInterface
     */
    private function getUrlBuilder()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(UrlInterface::class);
    }
}
