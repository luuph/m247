<?php
namespace Unveels\BlogCategoryBanner\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CategoryImage implements ResolverInterface
{
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/categoryImage.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');
        if (!isset($value['category_id'])) {
            return null;
        }

        $catBlogImg = $value['cat_blog_img'];
        return $catBlogImg;
    }

}
