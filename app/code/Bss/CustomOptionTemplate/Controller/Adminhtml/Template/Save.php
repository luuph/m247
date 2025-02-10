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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Controller\Adminhtml\Template;

use Bss\CustomOptionTemplate\Model\Config;
use Bss\CustomOptionTemplate\Model\Config\Source\SaveMode;
use Bss\CustomOptionTemplate\Model\Publisher;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends Action
{
    /**
     * @var Config
     */
    protected $modelConfig;

    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomOptionTemplate::grid';

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory
     * @param \Bss\CustomOptionTemplate\Model\Initialization\Helper $initializationHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param TypeListInterface $typeList
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\Config $modelConfig,
        \Bss\CustomOptionTemplate\Model\Publisher $publisher,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Bss\CustomOptionTemplate\Model\TemplateFactory $templateFactory,
        \Bss\CustomOptionTemplate\Model\Initialization\Helper $initializationHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        TypeListInterface $typeList
    ) {
        $this->modelConfig = $modelConfig;
        $this->publisher = $publisher;
        parent::__construct($context);
        $this->jsHelper = $jsHelper;
        $this->productFactory = $productFactory;
        $this->templateFactory = $templateFactory;
        $this->initializationHelper = $initializationHelper;
        $this->json = $json;
        $this->typeList = $typeList;
    }

    /**
     * Save Template
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $dataPost = $this->getRequest()->getPostValue();
        //handle insert conditions data to label array
        if (isset($dataPost['rule']['conditions'])) {
            $dataPost['template']['conditions'] = $dataPost['rule']['conditions'];
            $ruleKeyPos = array_search('rule', array_keys($dataPost));
            array_splice($dataPost, $ruleKeyPos,1);
        }
        // fix for before data of older version
        if (isset($dataPost['parameters']['conditions'])) {
            $dataPost['template']['conditions'] += $dataPost['parameters']['conditions'];
        }

        $data = $dataPost['template'];
        $id = isset($data['template_id']) ? $data['template_id'] : null;
        $options = isset($dataPost['product']['options']) ? $dataPost['product']['options'] : [];
        $optionDeleted = [];
        foreach ($options as $option) {
            if (!empty($option['is_delete'])) {
                $optionDeleted[] = $option['is_delete'];
            }
        }

        $template = $this->templateFactory->create()->load($id);
        $productIdsSaved = $template->getProductIds();
        $template->loadPost($data);

        //add Product Ids and number of product apply
        $productIds = $template->filterProducts();
        $template->setProductIds($productIds);
        $template->setApplyTo(count(array_filter(explode(",", $productIds))));
        $productIdsDelete = $this->checkProductsDelete($productIdsSaved, $productIds);


        if (!empty($options) && (count($options) != count($optionDeleted))) {
            try {
                $template->save();
                if ( $this->initializationHelper->saveCustomOptionTemplate($options, $template->getId()) &&
                    $this->modelConfig->getConfigSaveMode() == SaveMode::UPDATE_ON_SAVE
                    ) {
                    $this->initializationHelper->deleteOptionOldProductAssign($productIdsDelete, $template->getId());

                }
                // add template options data
                $template->setOptionTemplateData($this->json->serialize($options), $template->getId());
                $this->messageManager->addSuccessMessage(__('The custom option template has been saved.'));
                //set cache FullPage cache invalid when save template
                $this->typeList->invalidate(
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                );
                $this->_getSession()->setFormData(false);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the custom option template.')
                );
            }

            $this->_getSession()->setFormData($dataPost);
            if ($this->modelConfig->getConfigSaveMode() == SaveMode::UPDATE_BY_SCHEDULE) {
                $this->publisher->execute(
                    $template->getId(),
                    $this->initializationHelper->getOptionsSave(),
                    $this->initializationHelper->getOptionsDelete(),
                    $productIdsDelete
                );
            }

            return $this->_getBackResultRedirect($resultRedirect, $template->getId());
        } else {
            $this->messageManager->addErrorMessage(__('The Custom Option is required !'));
            return $resultRedirect->setRefererUrl();
        }
    }

    /**
     * Check if unassign product. Get productids unassign
     *
     * @param string $productIdsSaved
     * @param string $productIdsPost
     * @return array
     */
    private function checkProductsDelete($productIdsSaved, $productIdsPost)
    {
        $productIdsOld = [];
        $productIdsSavedArray = $productIdsSaved ? explode(',', $productIdsSaved): [];
        $productIdsPostArray = $productIdsPost ? explode(',', $productIdsPost) : [];
        if (!empty($productIdsSavedArray)) {
            $productIdsOld = array_diff($productIdsSavedArray, $productIdsPostArray);
        }
        return $productIdsOld;
    }

    /**
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @param null $paramCrudId
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _getBackResultRedirect(
        \Magento\Framework\Controller\Result\Redirect $resultRedirect,
                                                      $paramCrudId = null
    ) {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'template_id' => $paramCrudId,
                        '_current' => true,
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new', ['_current' => true]);
                break;
            default:
                $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }
}
