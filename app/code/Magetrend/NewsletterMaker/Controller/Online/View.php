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

namespace Magetrend\NewsletterMaker\Controller\Online;

use \Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Event\Magento;

/**
 * Viev template online controller class
 */
class View extends \Magento\Framework\App\Action\Action
{

    public $templateFactory;

    public $subscriberFactory;

    public $queueFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\QueueFactory $queueFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->queueFactory = $queueFactory;
        parent::__construct($context);
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $nId = $this->getRequest()->getParam('nid');
        $templateText = '';

        $sorryMessage = __('Unfortunately, the newsletter online preview is not available');
        try {
            if (empty($id) && empty($nId)) {
                throw new LocalizedException($sorryMessage);
            }

            $key = !empty($id)?$id:$nId;
            $tmpKey = explode('_', $key);
            if (!is_numeric($tmpKey[0])) {
                throw new LocalizedException($sorryMessage);
            }

            $templateId = $tmpKey[0];
            unset($tmpKey[0]);
            $subscriberCode = implode('_', $tmpKey);

            if (!empty($id)) {
                $queue = $this->queueFactory->create()
                    ->load($templateId);
                if (!$queue->getId()) {
                    throw new LocalizedException($sorryMessage);
                }
                $templateId = $queue->getTemplateId();
                $templateText = $queue->getNewsletterText();
            }

            $subscriber = $this->subscriberFactory->create()
                ->load($subscriberCode, 'subscriber_confirm_code');

            $template = $this->templateFactory->create()
                ->load($templateId);

            if (!$template || !$template->getId() || !$subscriber || !$subscriber->getId()) {
                throw new LocalizedException($sorryMessage);
            }

            if (!empty($templateText)) {
                $template->setTemplateText($templateText);
            }

            $layout = $this->_view->getLayout();
            $block = $layout->createBlock(\Magento\Backend\Block\Template::class);
            $block->setNewsletterTemplate($template)
                ->setSubscriber($subscriber)
                ->setTemplate('Magetrend_NewsletterMaker::online/view.phtml');
            $this->getResponse()->setBody($block->toHtml());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('/');
        } catch (\Exception $e) {
            $this->_redirect('/');
        }
    }
}
