<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Customer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class PrintInvoice extends AbstractCustomer implements ResolverInterface
{
    /**
     * DateTo variable
     *
     * @var string
     */
    protected $dateTo;
    
    /**
     * DateFrom variable
     *
     * @var string
     */
    protected $dateFrom;

    /**
     * InvoiceId variable
     *
     * @var mixed
     */
    protected $invoiceId;

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        
        try {
            $this->verifyRequest();
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            try {
                if ($this->invoiceId != 0 && $this->incrementId !=0) {
                    $order = $this->order->loadByIncrementId($this->incrementId);
                    if ($order->getCustomerId() == $this->customerId) {
                        $invoiceDetails = $this->_initInvoice($this->invoiceId, $order);
                        $invoice[] = $invoiceDetails['invoice'];
                        $pdf = $this->invoicePdf->getPdf($invoice);
                        $rootPath  =  $this->baseDir;
                        $fileName = sprintf('invoiceslip%s.pdf', $this->date->date('Y-m-d_H-i-s'));
                        $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
                        $mediaDirectory->writeFile($rootPath . "/" . $fileName, $pdf->render());
                        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        );
                        $this->returnArray = [
                            'success' => true,
                            'message' => __(''),
                            'url'=> $mediaUrl . $fileName
                        ];
                    } else {
                        $this->returnArray["message"] = __("You are not authorize to view this invoice.");
                    }
                }
                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->returnArray["message"] = $e->getMessage();
            } catch (\Exception $e) {
                $this->returnArray["message"] = __("We can't print the invoice right now.");
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->returnArray;
        }
    }
    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 1;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
            $this->invoiceId     = $this->wholeData["invoiceId"]     ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken == "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
            if (!isset($this->incrementId)) {
                $this->incrementId = 0;
            }
            if (!isset($this->invoiceId)) {
                $this->invoiceId = 0;
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Initialize invoice model instance.
     *
     * @param int                        $invoiceId invoice Id
     * @param \Magento\Sales\Model\Order $order     order
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    private function _initInvoice($invoiceId, $order)
    {
        $data = [];
        $data['success'] = false;
        if (!$invoiceId) {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceRepository->get($invoiceId);
        if (!$invoice) {
            throw new \BadMethodCallException(__("Invalid Invoice Id"));
        }
        try {
            // $tracking = $this->order->load($order->getId());
            $tracking = $order->getInvoiceCollection()->getFirstItem();
            if ($tracking && $tracking->getId()) {
                if ($tracking->getId() == $invoiceId) {
                    if (!$invoiceId) {
                        $data['message'] = __("The invoice no longer exists.");
                        throw new \BadMethodCallException(__($data['message']));
                    }
                } else {
                    $data['message'] = __("You are not authorize to view this invoice.");
                    throw new \BadMethodCallException(__($data['message']));
                }
            } else {
                $data['message'] = __("You are not authorize to view this invoice.");
                throw new \BadMethodCallException(__($data['message']));
            }
        } catch (\NoSuchEntityException $e) {
            throw new \BadMethodCallException(__($e->getMessage()));
        } catch (\InputException $e) {
            throw new \BadMethodCallException(__($e->getMessage()));
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_invoice', $invoice);
        $data['success'] = true;
        $data['invoice'] = $invoice;
        $data['tracking'] = $tracking;
        return $data;
    }
}
