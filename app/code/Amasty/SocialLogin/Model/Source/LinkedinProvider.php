<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LinkedinProvider implements OptionSourceInterface
{
    public const OAUTH2 = 'oauth2';
    public const OPENID = 'openid';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::OAUTH2,
                'label' => __('LinkedIn (Deprecated)')
            ],
            [
                'value' => self::OPENID,
                'label' => __('LinkedIn OpenID Connect')
            ]
        ];
    }
}
