<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Block\Adminhtml\RestrictPaymentMethod\Form;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 */
class AssignCustomer extends \Magento\Backend\Block\Template
{

    protected $_template = 'FME_RestrictPaymentMethod::customers/assign-customers.phtml';
    protected $blockGrid;
    protected $registry;
    protected $jsonEncoder;
    protected $_paymentmethodFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \FME\RestrictPaymentMethod\Model\PaymentMethod $paymentmethodFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->_paymentmethodFactory = $paymentmethodFactory;
        parent::__construct($context, $data);
    }
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'FME\RestrictPaymentMethod\Block\Adminhtml\RestrictPaymentMethod\Form\Tab\Customers',
                'related.customer'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getCustomersJson()
    {
        $customers = $this->getPaymentMethod();
        if (!empty($customers)) {
            return $customers;
        }
        return '{}';
    }

    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getPaymentMethod()
    {
        $id = $this->getRequest()->getParam('id');
        $customerlist=$this->_paymentmethodFactory->getRelatedCustomers($id);
        foreach ($customerlist as $customersids) {
            $customer[]=$customersids['entity_id'];
        }
        return $this->_paymentmethodFactory->Load($id)->getCustomers();
    }
}
