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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Helper;

use Magento\Framework\App\Helper\Context;

class HelperController extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;
    /**
     * HelperController constructor.
     * @param Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @param array $customOptionData
     * @return array|mixed
     */
    public function setTitleForStores($customOptionData)
    {
        $dataTitles = [];
        if (isset($customOptionData['title_option']) && $customOptionData['title_option'] != "") {
            $dataTitles = $this->json->unserialize($customOptionData['title_option']);
        }
        $dataTitles[0] = $customOptionData['title'];
        return $dataTitles;
    }

    /**
     * @param string $titleOption
     * @param string $titleGlobal
     * @return array|mixed
     */
    public function setTitleValuesForStores($titleOption, $titleGlobal)
    {
        $dataTitles = [];
        if ($titleOption) {
            $dataTitles = $this->json->unserialize($titleOption);
        }
        $dataTitles[0] = $titleGlobal;
        return $dataTitles;
    }

    /**
     * @param int $isDefault
     * @param int $optionTypeId
     * @return array
     */
    public function setIsDefaultValues($isDefault, $optionTypeId)
    {
        $data = [];
        $data['option_type_id'] = $optionTypeId;
        $data['is_default'] = $isDefault;
        return $data;
    }

    /**
     * @param array $customOptionData
     * @param int $id
     * @return array
     */
    public function setVisibleOptionByCustomer($customOptionData, $id)
    {
        $data = [];
        $data['option_id'] = $id;
        $data['visible_for_group_customer'] = isset($customOptionData['visibility']['customer_group'])
            ? $customOptionData['visibility']['customer_group'] : '';
        return $data;
    }

    /**
     * @param array $customOptionData
     * @param int $id
     * @return array
     */
    public function setVisibleOptionByStore($customOptionData, $id)
    {
        $data = [];
        $data['option_id'] = $id;
        $data['visible_for_store_view'] = isset($customOptionData['visibility']['stores'])
            ? $customOptionData['visibility']['stores'] : '';
        return $data;
    }
}
