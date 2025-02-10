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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Api;

use Bss\DynamicCategory\Api\Data\RuleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface RuleRepositoryInterface
{

    /**
     * Save Rule
     *
     * @param RuleInterface $rule
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface
     * @throws LocalizedException
     */
    public function save(RuleInterface $rule);

    /**
     * Retrieve Rule
     *
     * @param int $ruleId
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface
     * @throws LocalizedException
     */
    public function get($ruleId);

    /**
     * Delete Rule
     *
     * @param RuleInterface $rule
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RuleInterface $rule);

    /**
     * Delete Rule by ID
     *
     * @param int $ruleId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($ruleId);
}
