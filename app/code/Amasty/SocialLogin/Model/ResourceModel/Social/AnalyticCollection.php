<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\ResourceModel\Social;

class AnalyticCollection extends Collection
{
    public function getSocialLoginData()
    {
        $this->getSelect()
            ->joinLeft(
                ['sales_item' => $this->getTable('amasty_sociallogin_sales')],
                'sales_item.social_id = main_table.social_id',
                [
                    'items' => 'SUM(sales_item.items)',
                    'amount' => 'SUM(sales_item.amount)'
                ]
            )->where(
                'main_table.customer_id IS NOT NULL'
            )->group('main_table.social_id');

        return $this->getItems();
    }
}
