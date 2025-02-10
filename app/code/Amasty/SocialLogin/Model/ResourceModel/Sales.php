<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sales extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_sociallogin_sales';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
