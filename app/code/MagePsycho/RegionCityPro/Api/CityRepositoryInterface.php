<?php

namespace MagePsycho\RegionCityPro\Api;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CityRepositoryInterface
{
    /**
     * Get city details by ID
     *
     * @param int $id
     * @return \MagePsycho\RegionCityPro\Api\Data\CityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Save city
     *
     * @param \MagePsycho\RegionCityPro\Api\Data\CityInterface $city
     * @return \MagePsycho\RegionCityPro\Api\Data\CityInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MagePsycho\RegionCityPro\Api\Data\CityInterface $city);

    /**
     * Get city list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MagePsycho\RegionCityPro\Api\Data\CitySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete city
     *
     * @param \MagePsycho\RegionCityPro\Api\Data\CityInterface $city
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\MagePsycho\RegionCityPro\Api\Data\CityInterface $city);

    /**
     * Delete city by ID
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);
}
