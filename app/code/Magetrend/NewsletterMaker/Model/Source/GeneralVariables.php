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

namespace Magetrend\NewsletterMaker\Model\Source;

/**
 * Store Contact Information source model
 */
class GeneralVariables implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Retrieve option array of store contact variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = $this->getData();
        if ($withGroup && $optionArray) {
            $optionArray = ['label' => __('General Information'), 'value' => $optionArray];
        }

        return $optionArray;
    }

    /**
     * Return available config variables
     * This method can be extended by plugin if you need to add more variables to the list
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return [
            ['value' => '{{var subscriber.getUnsubscriptionLink()}}', 'label' => __('Unsubscribe Link')],
            ['value' => '{{var mt.getOnlineLink($subscriber)}}', 'label' => __('View Online Link')],
        ];
    }
}
