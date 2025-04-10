<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Authorization\Model\CompositeUserContext;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\AffiliatePro\Model\Banner\Status;
use Mageplaza\Affiliate\Model\Account;
use Mageplaza\Affiliate\Model\Campaign;
use Mageplaza\AffiliatePro\Api\BannerRepositoryInterface;
use Mageplaza\AffiliatePro\Api\Data\BannerSearchResultInterface;
use Mageplaza\AffiliatePro\Api\Data\BannerSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\AffiliatePro\Helper\Data;
use Mageplaza\AffiliatePro\Model\Api\BannerFactory as BannerAPIFactory;

/**
 * Class BannerRepository
 * @package Mageplaza\AffiliatePro\Model
 */
class BannerRepository implements BannerRepositoryInterface
{
    /**
     * @var CompositeUserContext
     */
    protected $userContext;

    /**
     * @var BannerAPIFactory
     */
    protected $bannerAPIFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var Campaign
     */
    protected $campaign;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @param CompositeUserContext $userContext
     * @param Data $helperData
     * @param BannerAPIFactory $bannerAPIFactory
     * @param SearchResultFactory $searchResultFactory
     * @param Campaign $campaign
     * @param CustomerFactory $customer
     */
    public function __construct(
        CompositeUserContext $userContext,
        Data                 $helperData,
        BannerAPIFactory     $bannerAPIFactory,
        SearchResultFactory  $searchResultFactory,
        Campaign             $campaign,
        CustomerFactory      $customer
    )
    {
        $this->userContext = $userContext;
        $this->bannerAPIFactory = $bannerAPIFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->campaign = $campaign;
        $this->helperData = $helperData;
        $this->customer = $customer;
    }

    /**
     * {@inheritDoc}
     * @throws InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Banner id required'));
        }

        $banner = $this->bannerAPIFactory->create()->load($id);

        if (!$banner->getId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        return $banner;
    }

    /**
     * Find entities by criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return BannerSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($id)
    {
        try {
            $this->get($id)->delete();
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the banner: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param null $storeId
     *
     * @return BannerSearchResultInterface|ResourceModel\Banner\Collection
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function getAffiliateBanner(SearchCriteriaInterface $searchCriteria, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = 1;
        }

        if (!$this->helperData->isEnabled($storeId)) {
            throw new CouldNotSaveException(__('The extension is disabled.'));
        }

        $affiliate = $this->getAffiliateAccount();
        if (!$affiliate->getId()) {
            throw new NoSuchEntityException(__('The customer %1 has not become an affiliate', $this->userContext->getUserId()));
        }

        $searchResult = $this->searchResultFactory->create();

        /** @var \Mageplaza\AffiliatePro\Model\ResourceModel\Banner\Collection $banner */
        $banner = $this->helperData->processGetList($searchCriteria, $searchResult);

        /** @var Account $affiliate */
        $affiliate = $this->getAffiliateAccount();

        $campaigns = $this->campaign->getCollection()
            ->getAvailableCampaign(
                $affiliate->getGroupId(),
                $this->customer->create()->load($affiliate->getCustomerId())->getWebsiteId()
            )
            ->getColumnValues('campaign_id');

        $banner->addFieldToFilter('campaign_id', ['in' => $campaigns])
            ->addFieldToFilter('status', Status::ENABLED);


        return $banner;
    }

    /**
     * @return Account
     */
    public function getAffiliateAccount()
    {
        $customerId = $this->userContext->getUserId();
        return $this->helperData->getAffiliateAccount($customerId, 'customer_id');
    }
}
