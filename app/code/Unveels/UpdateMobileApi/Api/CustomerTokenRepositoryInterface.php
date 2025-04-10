<?php

namespace Unveels\UpdateMobileApi\Api;

use Magento\Framework\Exception\LocalizedException;

interface CustomerTokenRepositoryInterface
{
    /**
     * Update customer mobile number by token
     *
     * @param string $customerToken
     * @param string $newMobile
     * @return bool
     * @throws LocalizedException
     */
    public function updateCustomerMobile($customerToken, $newMobile);
}
