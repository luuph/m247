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

namespace Magetrend\NewsletterMaker\Plugin\Newsletter\Model;

class Queue
{

    public $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function beforeSendPerSubscriber($queue, $count = 20)
    {
        $this->registry->unregister(\Magetrend\NewsletterMaker\Helper\Variables::REGISTRY_QUEUE_ID);
        $this->registry->register(\Magetrend\NewsletterMaker\Helper\Variables::REGISTRY_QUEUE_ID, $queue->getId());

        return [ $count ];
    }
}
