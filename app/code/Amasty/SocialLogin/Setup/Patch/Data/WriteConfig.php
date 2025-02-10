<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class WriteConfig implements DataPatchInterface
{
    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        WriterInterface $writer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->writer = $writer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return
     */
    public function apply()
    {
        if ($this->scopeConfig->getValue('amsociallogin/general/use_new_url') === null) {
            $this->writer->save('amsociallogin/general/use_new_url', 1);
        }
        
        return $this;
    }
}
