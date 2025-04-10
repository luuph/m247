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

namespace Magezon\LookBook\Ui\DataProvider\Profile\Form;

use Magento\Framework\Url\ModifierInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory;

class DataProvider extends \Magezon\Core\Ui\DataProvider\Form\AbstractModifier
{
    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @param string                      $name
     * @param string                      $primaryFieldName
     * @param string                      $requestFieldName
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory           $profileCollectionFactory
     * @param DataPersistorInterface      $dataPersistor
     * @param PoolInterface               $pool
     * @param array                       $meta
     * @param array                       $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Registry $registry,
        CollectionFactory $profileCollectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $profileCollectionFactory->create();
        $this->registry      = $registry;
        $this->dataPersistor = $dataPersistor;
        $this->pool          = $pool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $profile = $this->getCurrentProfile();
        if ($profile && $profile->getId()) {
            $this->loadedData[$profile->getId()] = $profile->getData();
        }

        $data = $this->dataPersistor->get('lookbook_profile');

        if (!empty($data)) {
            $profile = $this->collection->getNewEmptyItem();
            $profile->setData($data);
            $this->loadedData[$profile->getId()] = $profile->getData();
            $this->dataPersistor->clear('lookbook_profile');
        }

        if (is_array($this->loadedData)) {
            $imageAttributes = ['image', 'og_img'];
            $original = reset($this->loadedData);
            foreach ($imageAttributes as $_attr) {
                $attributeTmp = $_attr . '_tmp';
                if (isset($original[$_attr]) && $original[$_attr] && $this->getFileInfo()->isExist($original[$_attr])) {
                    $data         = [];
                    $fileName     = $original[$_attr];
                    $stat         = $this->getFileInfo()->getStat($fileName);
                    $mime         = $this->getFileInfo()->getMimeType($fileName);
                    $data['name'] = $fileName;
                    $data['url']  = $this->getFileInfo()->getFileUrl($fileName);
                    $data['size'] = isset($stat) ? $stat['size'] : 0;
                    $data['type'] = $mime;
                    $original[$attributeTmp] = $data;
                } else {
                    unset($original[$attributeTmp]);
                }
            }
            $key = key($this->loadedData);
            $this->loadedData[$key] = $original;
        }

        if (is_array($this->loadedData)) {
            $data = reset($this->loadedData);
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $data = $modifier->modifyData($data);
            }
            $key = key($this->loadedData);
            $this->loadedData[$key] = $data;
        }

        return $this->loadedData;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }


    /**
     * Get current profile
     *
     * @return \Magezon\LookBook\Model\Profile
     * @throws NoSuchEntityException
     */
    public function getCurrentProfile()
    {
        return $this->registry->registry('current_profile');
    }
}