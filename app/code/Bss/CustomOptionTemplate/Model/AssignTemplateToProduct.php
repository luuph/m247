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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Model;

use Bss\CustomOptionTemplate\Model\Initialization\Helper;
use Bss\CustomOptionTemplate\Model\ResourceModel\Template\CollectionFactory;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

class AssignTemplateToProduct
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Helper
     */
    protected $initializationHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param Helper $initializationHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        CollectionFactory  $collectionFactory,
        Json               $json,
        Helper             $initializationHelper,
        ResourceConnection $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->json = $json;
        $this->initializationHelper = $initializationHelper;
        $this->resource = $resource;
    }

    /**
     * Catalog Product After Save by Api
     *
     * @param ProductInterface $product
     * @return void
     * @throws LocalizedException
     */
    public function setTemplateToProduct($product)
    {
        $productId = $product->getId();
        $templateCollection = $this->collectionFactory->create();
        $templateOptionsData = $updateTemplate = [];
        $addTemplate = $removeTemplate = [];
        $product->setData('check_bss_template', 'ok');
        foreach ($templateCollection as $template) {
            if ($template->getOptionsData()) {
                $templateOptionsData[$template->getId()] = $this->json->unserialize($template->getOptionsData());
                $listProductIdsOld = $template->getProductIds() ? explode(",", $template->getProductIds()) : [];
                if ($template->validate($product)) {
                    if (!in_array($productId, $listProductIdsOld)) {
                        $addTemplate[] = $template->getId();
                        $listProductIdsOld[] = $productId;
                        $updateTemplate[$template->getId()] = [
                            'product_ids' => implode(",", $listProductIdsOld),
                            'apply_to' => $this->getCountOfListProductIds($listProductIdsOld)
                        ];
                    }
                } else {
                    if (in_array($productId, $listProductIdsOld)) {
                        $removeTemplate[] = $template->getId();
                        unset($listProductIdsOld[array_search($productId, $listProductIdsOld)]);
                        $updateTemplate[$template->getId()] = [
                            'product_ids' => implode(",", $listProductIdsOld),
                            'apply_to' => $this->getCountOfListProductIds($listProductIdsOld)
                        ];
                    }
                }
            }
        }
        $product->setData('check_bss_template', 'notok');
        $addNewTemplateFromExclude = $product->getData('add_new_template_from_exclude');
        //add options from template
        if ($addNewTemplateFromExclude) {
            $this->addOptionsFromTemplate($productId, $addNewTemplateFromExclude, $templateOptionsData);
        }
        $product->setData('add_new_template_from_exclude', []);

        //remove option
        $this->deleteOptionsFromTemplate($productId, $removeTemplate, $templateOptionsData);

        //add options from template
        $this->addOptionsFromTemplate($productId, $addTemplate, $templateOptionsData);

        //update product_ids and apply_to of template
        $this->updateTemplateData($updateTemplate);
    }

    /**
     * Catalog Product After Save by Api
     *
     * @param int $productId
     * @return void
     * @throws LocalizedException
     */
    public function unsetTemplateToProduct($productId)
    {
        $templateCollection = $this->collectionFactory->create();
        $updateTemplate = [];
        foreach ($templateCollection as $template) {
            if ($template->getOptionsData()) {
                $templateOptionsData[$template->getId()] = $this->json->unserialize($template->getOptionsData());
                $listProductIdsOld = $template->getProductIds() ? explode(",", $template->getProductIds()) : [];
                $key = array_search($productId, $listProductIdsOld);
                if ($key) {
                    unset($listProductIdsOld[$key]);
                    $updateTemplate[$template->getId()] = [
                        'product_ids' => implode(",", $listProductIdsOld),
                        'apply_to' => $this->getCountOfListProductIds($listProductIdsOld)
                    ];
                }
            }
        }

        //update product_ids and apply_to of template
        $this->updateTemplateData($updateTemplate);
    }

    /**
     * Count list product id
     *
     * @param array $listProductIdsOld
     * @return int|void
     */
    private function getCountOfListProductIds($listProductIdsOld)
    {
        return count($listProductIdsOld);
    }

    /**
     * Add option from template
     *
     * @param int $productId
     * @param array $addTemplate
     * @param array $templateOptionsData
     * @throws Exception
     */
    protected function addOptionsFromTemplate($productId, $addTemplate, $templateOptionsData)
    {
        foreach ($addTemplate as $templateId) {
            if (!empty($templateOptionsData[$templateId])) {
                $this->initializationHelper->saveCustomOptionTemplate(
                    $templateOptionsData[$templateId],
                    $templateId,
                    $productId
                );
            }

        }
    }

    /**
     * Delete option from template
     *
     * @param int $productId
     * @param array $removeTemplate
     * @param array $templateOptionsData
     * @throws LocalizedException
     */
    protected function deleteOptionsFromTemplate($productId, $removeTemplate, $templateOptionsData)
    {
        foreach ($removeTemplate as $templateId) {
            $this->initializationHelper->deleteOptionOldProductAssign(
                [$productId],
                $templateId,
                $templateOptionsData[$templateId]
            );
        }
    }

    /**
     * Update template data
     *
     * @param array $updateTemplate
     */
    protected function updateTemplateData($updateTemplate)
    {
        if (!empty($updateTemplate)) {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('bss_custom_option_template');
            foreach ($updateTemplate as $id => $template) {
                $data = [
                    'product_ids' => $template['product_ids'],
                    'apply_to' => $template['apply_to']
                ];
                $connection->update($table, $data, ['template_id = ?' => $id]);
            }
        }
    }
}
