<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\AppCreator;

/**
 * Class Update for AppCreator
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * PageFactory variable
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    
    /**
     * CollectionFactory variable
     *
     * @var \Webkul\MobikulCore\Model\ResourceModel\Appcreator\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * AppcreatorFactory variable
     *
     * @var \Webkul\MobikulCore\Model\AppcreatorFactory
     */
    protected $_appcreatorFactory;
    
    /**
     * ResultJsonFactory variable
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * Construct function
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Appcreator\CollectionFactory $collectionFactory
     * @param \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Appcreator\CollectionFactory $collectionFactory,
        \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_appcreatorFactory = $appcreatorFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    /**
     * Function to get the image url.
     *
     * @param string $id
     * @param string $type
     * @return void
     */
    public function getImageName($id = "dynamic", $type = "category")
    {
        switch (true) {
            case ($id == 'featuredcategories' && $type == 'category'):
                return 'category.jpg';
            case ($id == 'hotDeals' && $type == 'product'):
                return 'slide.jpg';
            case ($id == 'newDeals' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'newProduct' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'bannerimage' && $type == 'banner'):
                return 'banner.jpg';
            case ($type == 'product'):
                return 'grid.jpg';
            case ($type == 'image'):
                return 'slide.jpg';
            default:
                return 'grid.jpg';
        }
    }

    /**
     * Execute Fucntion
     *
     * @return jSon
     */
    public function execute()
    {
        try {
            $resultJson = $this->resultJsonFactory->create();
            $_data = [];
            $mobileViewData = [];
            $layoutSortingData = [];
            if (!empty($this->getRequest()->getParams()['data'])) {
                $layoutSortingData = explode(',', $this->getRequest()->getParams()['data']);
                $websiteId = $this->getRequest()->getParams()['website_id'];
            }
            foreach ($layoutSortingData as $key => $data) {
                $data1 = explode('_', $data);
                $data = trim($data1[0], " ");
                $mobileViewData[] = [
                    'imagePath' => $this->getImageName($data, trim($data1[1], " ")),
                    'label' => rawurldecode($data1[2]) // @codingStandardsIgnoreLine
                ];
                if (array_key_exists($data, $_data)) {
                    $_data[$data]['position'] .= ($key+1).',';
                    continue;
                }
                $posi = ($key+1).',';
                $_data[$data] = [
                    'layout_id'=>$data,
                    'label'=> rawurldecode($data1[2]), // @codingStandardsIgnoreLine
                    'position'=>$posi,
                    'type' => $data1[1],
                    'website_id' => $websiteId
                ];
            }
            $post = $this->_appcreatorFactory->create();
            $currentlayouts = $post->getCollection()->addFieldToFilter('website_id', $websiteId);
            if (!empty($currentlayouts)) {
                foreach ($currentlayouts as $currentlayout) {
                    $delete_id = $currentlayout->getId();
                    $post->load($delete_id)->delete();
                }
            }
            $connection = $post->getResource()->getConnection();
            foreach ($_data as $dataTosave) {
                $model = $this->_appcreatorFactory->create();
                $model->setData($dataTosave);
                $model->save();
            }
            $this->_eventManager->dispatch(
                'invalidate_mobikul_catalog_cache'
            );
            $response = ['success' => true, 'data' => $mobileViewData];
            return $resultJson->setData($response);
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => null];
        }
    }

    /**
     * Fucntion to check if this controller can be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::appcreator");
    }
}
