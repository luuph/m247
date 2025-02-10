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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Plugin;

use Magento\Email\Model\AbstractTemplate;

class AddVariableQuoteExtensionSaleOrderEmailTemplate
{
    /**
     * Add variable quote extension increment id to sale order email template
     *
     * @param AbstractTemplate $subject
     * @param AbstractTemplate $result
     * @param string $templateId
     * @return AbstractTemplate
     */
    public function afterLoadDefault(AbstractTemplate $subject, $result, $templateId)
    {
        // TODO: Implement plugin method.
        if (substr($templateId, 0, 11) === 'sales_email') {
            $result->setData(
                'orig_template_variables',
                substr(
                    $result->getData('orig_template_variables'),
                    0,
                    strlen($result->getData('orig_template_variables'))- 1
                ) . ', "var bss_quote_extension_increment_id" : "Quote #"}'
            );
        }
        return $result;
    }
}
