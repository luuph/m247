<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Controller\Adminhtml\Queue;

class Save
{
    /**
     * @var \Magetrend\NewsletterMaker\Helper\Data
     */
    public $mtHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var
     */
    public $collectionFactory;

    /**
     * Save constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magetrend\NewsletterMaker\Helper\Data $helper
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magetrend\NewsletterMaker\Helper\Data $helper,
        \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
    ) {
        $this->mtHelper = $helper;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    public function afterExecute($controller, $results)
    {
        $request = $controller->getRequest();
        $isMtEmail = $request->getParam('is_mtemail');
        $templateId = $request->getParam('template_id');

        if ($isMtEmail != 1 || !is_numeric($templateId)) {
            return $results;
        }

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('template_id', $templateId)
            ->addFieldToFilter('is_mtemail', 0);

        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $item->setIsMtemail(1);
            }
            $collection->walk('save');
        }

        return $results;
    }
}
