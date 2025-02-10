<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Model\Validator;

/**
 * Regex validator config
 */
class Config
{
    /**
     * Patterns to match strings for validator
     *
     * @var string[]
     */
    protected $patterns;

    /**
     * Patterns to match strings for validator
     *
     * @var string[]
     */
    protected $requires;

    /**
     * @param string[] $patterns
     * @param string[] $requires
     */
    public function __construct($patterns = [], $requires = [])
    {
        $this->patterns = $patterns;
        $this->requires = $requires;
    }

    /**
     * Retrieve translation patterns
     *
     * @return string[]
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * Retrieve translation patterns
     *
     * @return string[]
     */
    public function getRequires()
    {
        return $this->requires;
    }
}
