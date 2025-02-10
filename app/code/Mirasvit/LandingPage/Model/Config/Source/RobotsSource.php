<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-landing-page
 * @version   1.0.13
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LandingPage\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RobotsSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => 'INDEX, FOLLOW',
                'value' => 'INDEX, FOLLOW',
            ],
            [
                'label' => 'NOINDEX, FOLLOW',
                'value' => 'NOINDEX, FOLLOW',
            ],
            [
                'label' => 'INDEX, NOFOLLOW',
                'value' => 'INDEX, NOFOLLOW',
            ],
            [
                'label' => 'NOINDEX, NOFOLLOW',
                'value' => 'NOINDEX, NOFOLLOW',
            ],
        ];
    }

}
