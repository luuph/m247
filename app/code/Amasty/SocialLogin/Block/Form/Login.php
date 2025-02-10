<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\ButtonLockManager;

class Login extends \Magento\Customer\Block\Form\Login
{
    /**
     * @return Login|\Magento\Customer\Block\Form\Login|\Magento\Framework\View\Element\AbstractBlock
     */
    public function _prepareLayout()
    {
        $parent = $this->getParentBlock();
        return $parent ? $parent->_prepareLayout() : $this;
    }

    /**
     * fix backward compatibility
     *
     * class ButtonLockManager added in Magento 2.4.7 through arguments in layout without backward compatibility
     *
     * @return ButtonLockManager|bool
     */
    public function getButtonLockManager()
    {
        try {
            return ObjectManager::getInstance()
                ->get(ButtonLockManager::class);
        } catch (\Exception $e) {
            return false;
        }
    }
}
