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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Validate;

class ValidateAttribute
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * ValidateAttribute constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * After execute
     *
     * @param Validate $subject
     * @param \Magento\Framework\Controller\Result\Json $result
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Validate $subject, $result)
    {
        $productData = $this->request->getPost('product', []);
        if (isset($productData['assign_by'])) {
            if ((int)$productData['assign_by'] === 2 && (int)$productData['receive_point'] <= 0) {
                return $result->setData(
                    [
                        'error' => true,
                        'messages' => [__('Please enter a number greater than 0 in field attribute "Number Point".')]
                    ]
                );
            }
        }
        return $result;
    }
}
