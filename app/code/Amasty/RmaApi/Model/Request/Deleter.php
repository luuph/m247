<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Request;

use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Api\RequestRepositoryInterface;
use Amasty\Rma\Model\Request\ResourceModel;
use Amasty\RmaApi\Api\RequestDeleterInterface;

class Deleter implements RequestDeleterInterface
{
    /**
     * @var ResourceModel\Request
     */
    private $requestResource;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(
        ResourceModel\Request $requestResource,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->requestResource = $requestResource;
        $this->requestRepository = $requestRepository;
    }

    public function deleteById(int $requestId): bool
    {
        $request = $this->requestRepository->getById($requestId);

        return $this->delete($request);
    }

    public function delete(RequestInterface $request): bool
    {
        $this->requestResource->delete($request);

        return true;
    }
}
