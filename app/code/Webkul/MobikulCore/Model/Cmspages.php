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

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsCollection;

/**
 * Class Cmspages model
 */
class Cmspages implements OptionSourceInterface
{
     /**
      * CmsPageCollection variable
      *
      * @var CmsCollection
      */
    protected $_cmsPageCollection;

    /**
     * Construct function
     *
     * @param CmsCollection $collection
     */
    public function __construct(
        CmsCollection $collection
    ) {
        $this->_cmsPageCollection = $collection;
    }

    /**
     * ToOptionArray function
     *
     * @return void
     */
    public function toOptionArray()
    {
        $collection = $this->_cmsPageCollection
            ->create()
            ->addFieldToFilter("is_active", 1)
            ->addFieldToFilter("identifier", ["nin"=>["no-route", "enable-cookies"]]);
        $returnData = [];
        foreach ($collection as $cms) {
            $returnData[] =  [
                "value" => $cms->getId(),
                "label" => $cms->getTitle()
            ];
        }
        return $returnData;
    }
}
