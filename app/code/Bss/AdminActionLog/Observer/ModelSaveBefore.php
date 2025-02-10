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
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Observer;

use Bss\AdminActionLog\Model\Log;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ModelSaveBefore implements ObserverInterface
{
    /**
     * @var \Bss\AdminActionLog\Model\Log
     */
    protected $logAction;

    /**
     * ModelSave constructor.
     * @param Log $logAction
     */
    public function __construct(
        \Bss\AdminActionLog\Model\Log $logAction
    ) {
        $this->logAction = $logAction;
    }

    /**
     * Prepare origin data before save
     *
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $action = $this->logAction->getAction();

        if ($this->isObjectSupportGenerateOldData($object) && is_array($action) && isset($action['expected_models'])) {
            if (is_string($action['expected_models']) && ($action['expected_models']) == get_class($object)) {
                if ($object->getOrigData() === null && $object->getId() !== null) {
                    $clonedObject = clone $object;
                    $orgData = $clonedObject->load($clonedObject->getId())->getData();
                    if ($object->getData('id') && !isset($orgData['id'])) {
                        $observer->getEvent()->getObject()->setOrigData('id', $object->getData('id'));
                    }
                    if (is_array($orgData)) {
                        foreach ($orgData as $key => $value) {
                            $observer->getEvent()->getObject()->setOrigData($key, $value);
                        }
                    }
                }
            }
        }
    }

    /**
     * Check some type object module support
     *
     * @param $object
     * @return bool
     */
    public function isObjectSupportGenerateOldData($object)
    {
        if ($object instanceof \Magento\Review\Model\Review ||
            $object instanceof \Magento\Review\Model\Rating ||
            $object instanceof \Magento\Tax\Model\Calculation\Rate ||
            $object instanceof \Magento\Customer\Model\Backend\Customer ||
            $object instanceof \Magento\CheckoutAgreements\Model\Agreement ||
            $object instanceof \Magento\Tax\Model\Calculation\Rule
        ) {
            return true;
        } else {
            return false;
        }
    }
}
