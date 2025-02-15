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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Component\MassAction;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

class Filter
{
    /**
     * Const
     */
    const SELECTED_PARAM = 'selected';
    const EXCLUDED_PARAM = 'excluded';
    const URL_REWRITE_ID = 'url_rewrite_id';

    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UiComponentInterface[]
     */
    protected $components = [];

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @param UiComponentFactory $factory
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        UiComponentFactory $factory,
        RequestInterface $request,
        FilterBuilder $filterBuilder
    ) {
        $this->factory = $factory;
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Returns component by namespace
     *
     * @return UiComponentInterface
     * @throws LocalizedException
     */
    public function getComponent()
    {
        $namespace = $this->request->getParam('namespace');
        if (!isset($this->components[$namespace])) {
            $this->components[$namespace] = $this->factory->create($namespace);
        }
        return $this->components[$namespace];
    }

    /**
     * Adds filters to collection using DataProvider filter results
     *
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getCollection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(self::SELECTED_PARAM);
        $excluded = $this->request->getParam(self::EXCLUDED_PARAM);

        $isExcludedIdsValid = (is_array($excluded) && !empty($excluded));
        $isSelectedIdsValid = (is_array($selected) && !empty($selected));

        if ('false' !== $excluded) {
            if (!$isExcludedIdsValid && !$isSelectedIdsValid) {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        }

        $filterIds = $this->getFilterIds();
        if (\is_array($selected)) {
            $filterIds = array_unique(array_merge($filterIds, $selected));
        }
        $collection->addFieldToFilter(
            'main_table.' . self::URL_REWRITE_ID,
            ['in' => $filterIds]
        );

        return $collection;
    }

    /**
     * Apply selection by Excluded Included to Search Result
     *
     * @throws LocalizedException
     * @return void
     */
    public function applySelectionOnTargetProvider()
    {
        $selected = $this->request->getParam(self::SELECTED_PARAM);
        $excluded = $this->request->getParam(self::EXCLUDED_PARAM);
        if ('false' === $excluded) {
            return;
        }
        $dataProvider = $this->getDataProvider();
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $this->filterBuilder->setConditionType('nin')
                    ->setField('main_table.'. $dataProvider->getPrimaryFieldName())
                    ->setValue($excluded);
                $dataProvider->addFilter($this->filterBuilder->create());
            } elseif (is_array($selected) && !empty($selected)) {
                $this->filterBuilder->setConditionType('in')
                    ->setField('main_table.'. $dataProvider->getPrimaryFieldName())
                    ->setValue($selected);
                $dataProvider->addFilter($this->filterBuilder->create());
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Applies selection to collection from POST parameters
     *
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function applySelection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(self::SELECTED_PARAM);
        $excluded = $this->request->getParam(self::EXCLUDED_PARAM);

        if ('false' === $excluded) {
            return $collection;
        }

        try {
            if (is_array($excluded) && !empty($excluded)) {
                $collection->addFieldToFilter(
                    'main_table . ' . $collection->getResource()->getIdFieldName(),
                    ['nin' => $excluded]
                );
            } elseif (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter(
                    'main_table . ' . $collection->getResource()->getIdFieldName(),
                    ['in' => $selected]
                );
            } else {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $collection;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }

    /**
     * Returns Referrer Url
     *
     * @throws LocalizedException
     * @return string|null
     */
    public function getComponentRefererUrl()
    {
        $data = $this->getComponent()->getContext()->getDataProvider()->getConfigData();
        return (isset($data['referer_url'])) ? $data['referer_url'] : null;
    }

    /**
     * Get data provider
     *
     * @throws LocalizedException
     * @return DataProviderInterface
     */
    protected function getDataProvider()
    {
        if (!$this->dataProvider) {
            $component = $this->getComponent();
            $this->prepareComponent($component);
            $this->dataProvider = $component->getContext()->getDataProvider();
        }
        return $this->dataProvider;
    }

    /**
     * Get filter ids as array
     *
     * @throws LocalizedException
     * @return int[]
     */
    protected function getFilterIds()
    {
        $idsArray = [];
        $this->applySelectionOnTargetProvider();
        if ($this->getDataProvider() instanceof \Magento\Ui\DataProvider\AbstractDataProvider) {
            // Use collection's getAllIds for optimization purposes.
            $idsArray = $this->getDataProvider()->getAllIds();
        } else {
            $dataProvider = $this->getDataProvider();
            $dataProvider->setLimit(0, false);
            $searchResult = $dataProvider->getSearchResult();
            // Use compatible search api getItems when searchResult is not a collection.
            foreach ($searchResult->getItems() as $item) {
                /** @var $item \Magento\Framework\Api\Search\DocumentInterface */
                $idsArray[] = $item->getData('url_rewrite_id');
            }
        }
        return  $idsArray;
    }
}
