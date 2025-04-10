<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api;

use Webkul\MobikulCore\Api\Data\WatchInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface WatchRepositoryInterface
 */
interface WatchRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $watchId Watch Id
     */
    public function getById($watchId);

    /**
     * Function deleteById
     *
     * @param integer $watchId watch id
     */
    public function deleteById($watchId);

    /**
     * Function save
     *
     * @param WatchInterface $watch watch
     */
    public function save(WatchInterface $watch);

    /**
     * Function delete
     *
     * @param WatchInterface $watch watch
     */
    public function delete(WatchInterface $watch);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria SearchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
