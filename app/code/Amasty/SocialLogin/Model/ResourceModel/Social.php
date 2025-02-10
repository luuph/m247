<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\ResourceModel;

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Social extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_sociallogin_customers';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * Return Social Network code.
     *
     * @param string|int $socialId
     */
    public function getTypeBySocialId($socialId): ?string
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), [SocialInterface::TYPE])
            ->where(SocialInterface::SOCIAL_ID . ' = ?', $socialId);

        $code = $connection->fetchOne($select);
        if (empty($code)) {
            return null;
        }
        
        return $code;
    }
}
