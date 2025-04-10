<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magezon\LookBook\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

   /**
    * @param ActionFactory                                                    $actionFactory
    * @param Registry                                                         $coreRegistry
    * @param \Magezon\LookBook\Helper\Data                                    $dataHelper
    * @param \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory  $profileCollectionFactory
    * @param \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    */
    public function __construct(
        ActionFactory $actionFactory,
        Registry $coreRegistry,
        \Magezon\LookBook\Helper\Data $dataHelper,
        \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->actionFactory             = $actionFactory;
        $this->coreRegistry              = $coreRegistry;
        $this->dataHelper                = $dataHelper;
        $this->profileCollectionFactory  = $profileCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            $pathInfo = trim($request->getPathInfo(), '/');
            $result   = $this->processUrlKey($request, $pathInfo);
            if ($result) {
                $request->setModuleName($result->getModuleName())
                    ->setControllerName($result->getControllerName())
                    ->setActionName($result->getActionName());
                if ($params = $result->getParams()) {
                    $request->setParams($params);
                }
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $pathInfo);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
        }
    }

    /**
     * @param  string $identifier
     * @return void
     */
    protected function processUrlKey($request, $identifier)
    {
        $result = false;
        $route  = $this->dataHelper->getRoute();
        if (!$route) {
            return;
        }
        $paths = explode("/", $identifier);
        $index = array_search($route, $paths);
        for ($i=0; $i < $index; $i++) {
            unset($paths[$i]);
        }
        $paths = array_values($paths);
        $count = count($paths);
        if ($paths[0] != $route) {
            return ;
        }

        if ($count == 1) {
            $result = new DataObject([
                'module_name'     => 'lookbook',
                'controller_name' => 'index',
                'action_name'     => 'index'
            ]);
        } else {
            $categoryRoute     = $this->dataHelper->getCategoryRoute();
            $keys              = array_filter([$categoryRoute, '']);
            $profileUseCategories = $this->dataHelper->getProfileUseCategories();
            if ($count == 2) {
                if ($profile = $this->getProfile($paths[1])) {
                    $categories = $profile->getCategoryList();
                        if (!$profileUseCategories || ($profileUseCategories && !count($categories))) {
                        $result = new DataObject([
                            'module_name'     => 'lookbook',
                            'controller_name' => 'profile',
                            'action_name'     => 'view',
                            'params' => [
                                'id' => $profile->getId()
                            ]
                        ]);
                        $this->coreRegistry->register("current_profile", $profile);
                    }
                }

                if (!$result && !$categoryRoute) {
                    $category = $this->getCategory($paths[1]);
                    if ($category) {
                        $result = new DataObject([
                            'module_name'     => 'lookbook',
                            'controller_name' => 'category',
                            'action_name'     => 'view',
                            'params' => [
                                'id' => $category->getId()
                            ]
                        ]);
                        $this->coreRegistry->register("current_category", $category);
                    }
                }
            }

            if ($count == 3) {

                if (in_array($paths[1], $keys)) {
                    switch ($paths[1]) {

                        //http://domain.com/lookbook/category/fashion
                        case $categoryRoute:
                            $cat = $this->getCategory($paths[2]);
                            if ($cat) {
                                $result = new DataObject([
                                    'module_name'     => 'lookbook',
                                    'controller_name' => 'category',
                                    'action_name'     => 'view',
                                    'params' => [
                                        'id' => $cat->getId()
                                    ]
                                ]);
                                $this->coreRegistry->register("current_category", $cat);
                            }
                            break;
                    }
                } elseif ($profileUseCategories) {
                    if ($profile = $this->getProfile($paths[2])) {
                        $category = $profile->getCategory();
                        if ($category && ($category->getIdentifier() == $paths[1])) {
                            $result = new DataObject([
                                'module_name'     => 'lookbook',
                                'controller_name' => 'profile',
                                'action_name'     => 'view',
                                'params' => [
                                    'id' => $profile->getId()
                                ]
                            ]);
                            $this->coreRegistry->register("current_profile", $profile);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return boolean
     */
    public function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }

    /**
     * @param $urlKey
     * @return \Magezon\LookBook\Model\Profile | \Magento\Framework\DataObject
     */
    private function getProfile($urlKey)
    {
        $urlSuffix = $this->dataHelper->getProfileUrlSuffix();

        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            $urlKey = !empty($urlSuffix) ? str_replace($urlSuffix, '', $urlKey) : $urlKey;
        }

        $collection = $this->profileCollectionFactory->create();
        $collection->prepareCollection();
        $collection->addFilterToMap('identifier', 'main_table.identifier');
        $collection->addFieldToFilter('identifier', $urlKey);

        $profile = $collection->getFirstItem();
        if ($profile->getId()) {
            return $profile;
        }
    }

    /**
     * @param $urlKey
     * @return \Magezon\LookBook\Model\Category | \Magento\Framework\DataObject
     */
    public function getCategory($urlKey)
    {
        $urlSuffix = $this->dataHelper->getCategoryUrlSuffix();
        if (($urlSuffix && $this->endsWith($urlKey, $urlSuffix)) || !$urlSuffix) {
            $urlKey = !empty($urlSuffix) ? str_replace($urlSuffix, '', $urlKey) : $urlKey;
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->prepareCollection();
        $collection->addFieldToFilter('identifier', $urlKey);
        $category = $collection->getFirstItem();
        if ($category->getId()) {
            return $category;
        }
    }
}
