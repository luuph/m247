<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

interface RequestDeleterInterface
{
    /**
     * @param int $requestId
     *
     * @return bool true on success
     */
    public function deleteById(int $requestId): bool;

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     *
     * @return bool true on success
     */
    public function delete(\Amasty\Rma\Api\Data\RequestInterface $request): bool;
}
