<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Api;

/**
 * WalletCreditRepository interface.
 * For managing wallet credit methods
 * @api
 */
interface WalletCreditRepositoryInterface
{
    /**
     * Create or update a credit rule.
     *
     * @param \Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface
     */
    public function save(\Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface $creditRule);

    /**
     * Get creditRule by creditRule Id
     *
     * @param int $entityId
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface
     */
    public function getById($entityId);

    /**
     * Delete creditRule.
     *
     * @param \Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return bool
     */
    public function delete(\Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface $creditRule);

    /**
     * Delete creditRule by ID.
     *
     * @param int $entityId
     * @return bool
     */
    public function deleteById($entityId);
}
