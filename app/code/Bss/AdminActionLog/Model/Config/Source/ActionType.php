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

namespace Bss\AdminActionLog\Model\Config\Source;

class ActionType implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get Option Array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            'view' => __('View'),
            'save' => __('Save'),
            'delete' => __('Delete'),
            'massDelete' => __('Mass Delete'),
            'export' => __('Export'),
            'import' => __('Import'),
            'edit' => __('Edit'),
            'massUpdate' => __('Mass Update'),
            'move' => __('Move'),
            'forgotpassword' => __('Forgot Password'),
            'global_search' => __('Global Search'),
            'apply' => __('Apply'),
            'reorder' => __('Reorder'),
            'print' => __('Print'),
            'email' => __('Email'),
            'run' => __('Run'),
            'rollback' => __('Rollback'),
            'reindex_process' => __('Reindex Process'),
            'clean' => __('Clean'),
            'flush' => __('Flush'),
            'fetch' => __('Fetch'),
            'login' => __('Login'),
            'send' => __('Send'),
            'create' => __('Create'),
            'inlineEdit' => __('Inline Edit'),
            'new' => __('New'),
            'freshRecent' => __('Fresh Recent'),
            'cron' => __('Cron')
        ];
    }

    /**
     * Get Action Type From Full Action
     *
     * @param string $fullActionName
     * @return string[]
     */
    public function getActionTypeFromFullAction($fullActionName)
    {
        $arr = explode('_', $fullActionName);
        $actionType = $arr[count($arr) - 1];
        $actionType = (str_contains(strtolower($actionType), 'save')) ? 'save' : $actionType;
        $actionType = (str_contains(strtolower($actionType), 'export')) ? 'export' : $actionType;
        if (!array_key_exists($actionType, $this->getOptionArray())) {
            $actionType = 'view';
        }
        return [
            $fullActionName => $actionType
        ];
    }

    /**
     * To Array
     *
     * @return string[]
     */
    public function toArray()
    {
        return [
            'catalog_product_edit' => 'view',
            'catalog_product_save' => 'save',
            'catalog_product_delete' => 'delete',
            'catalog_product_massStatus' => 'massUpdate',
            'catalog_product_massDelete' => 'massDelete',
            'catalog_product_action_attribute_save' => 'massUpdate',
            'catalog_category_edit' => 'view',
            'catalog_category_save' => 'save',
            'catalog_category_move' => 'move',
            'catalog_category_delete' => 'delete',
            'adminhtml_url_rewrite_edit' => 'view',
            'adminhtml_url_rewrite_save' => 'save',
            'adminhtml_url_rewrite_delete' => 'delete',
            'catalog_search_edit' => 'view',
            'catalog_search_save' => 'save',
            'catalog_search_delete' => 'delete',
            'catalog_search_massDelete' => 'massDelete',
            'adminhtml_index_globalSearch' => 'global_search',
            'rating_edit' => 'view',
            'rating_save' => 'save',
            'rating_delete' => 'delete',
            'review_product_edit' => 'view',
            'review_product_save' => 'save',
            'review_product_post' => 'save',
            'review_product_delete' => 'delete',
            'review_product_massUpdateStatus' => 'massUpdate',
            'review_product_massDelete' => 'massDelete',
            'catalog_product_attribute_edit' => 'view',
            'catalog_product_attribute_save' => 'save',
            'catalog_product_attribute_delete' => 'delete',
            'catalog_product_set_edit' => 'view',
            'catalog_product_set_save' => 'save',
            'catalog_product_set_delete' => 'delete',
            'adminhtml_auth_forgotpassword' => 'forgotpassword',
            'cms_page_edit' => 'view',
            'cms_page_save' => 'save',
            'cms_page_delete' => 'delete',
            'adminhtml_cms_page_edit' => 'view',
            'adminhtml_cms_page_save' => 'save',
            'adminhtml_cms_page_delete' => 'delete',
            'cms_block_edit' => 'view',
            'cms_block_save' => 'save',
            'cms_block_delete' => 'delete',
            'customer_index_edit' => 'view',
            'customer_index_save' => 'save',
            'customer_index_validate' => 'save',
            'customer_index_delete' => 'delete',
            'customer_index_massSubscribe' => 'massUpdate',
            'customer_index_massUnsubscribe' => 'massUpdate',
            'customer_index_massDelete' => 'massDelete',
            'customer_index_exportCsv' => 'export',
            'customer_index_exportXml' => 'export',
            'mui_export_gridToXml' => 'export',
            'mui_export_gridToCsv' => 'export',
            'customer_index_massAssignGroup' => 'massUpdate',
            'customer_group_edit' => 'view',
            'customer_group_save' => 'save',
            'customer_group_delete' => 'delete',
            'reports_report_sales_sales' => 'view',
            'reports_report_sales_tax' => 'view',
            'reports_report_sales_shipping' => 'view',
            'reports_report_sales_invoiced' => 'view',
            'reports_report_sales_refunded' => 'view',
            'reports_report_sales_coupons' => 'view',
            'reports_report_shopcart_product' => 'view',
            'reports_report_shopcart_abandoned' => 'view',
            'reports_report_product_sold' => 'view',
            'reports_report_product_ordered' => 'view',
            'reports_report_product_viewed' => 'view',
            'reports_report_product_lowstock' => 'view',
            'reports_report_product_downloads' => 'view',
            'reports_report_customer_accounts' => 'view',
            'reports_report_customer_orders' => 'view',
            'reports_report_customer_totals' => 'view',
            'reports_report_review_customer' => 'view',
            'reports_report_review_product' => 'view',
            'reports_index_search' => 'view',
            'invitations_report_invitation_index' => 'view',
            'invitations_report_invitation_customer' => 'view',
            'invitations_report_invitation_order' => 'view',
            'reports_report_sales_exportSalesCsv' => 'export',
            'reports_report_sales_exportSalesExcel' => 'export',
            'reports_report_sales_exportTaxCsv' => 'export',
            'reports_report_sales_exportTaxExcel' => 'export',
            'reports_report_sales_exportShippingCsv' => 'export',
            'reports_report_sales_exportShippingExcel' => 'export',
            'reports_report_sales_exportInvoicedCsv' => 'export',
            'reports_report_sales_exportInvoicedExcel' => 'export',
            'reports_report_sales_exportRefundedCsv' => 'export',
            'reports_report_sales_exportRefundedExcel' => 'export',
            'reports_report_sales_exportCouponsCsv' => 'export',
            'reports_report_sales_exportCouponsExcel' => 'export',
            'reports_report_shopcart_exportProductCsv' => 'export',
            'reports_report_shopcart_exportProductExcel' => 'export',
            'reports_report_shopcart_exportAbandonedCsv' => 'export',
            'reports_report_shopcart_exportAbandonedExcel' => 'export',
            'reports_report_product_exportOrderedCsv' => 'export',
            'reports_report_product_exportOrderedExcel' => 'export',
            'reports_report_product_exportViewedCsv' => 'export',
            'reports_report_product_exportViewedExcel' => 'export',
            'reports_report_product_exportSoldCsv' => 'export',
            'reports_report_product_exportSoldExcel' => 'export',
            'reports_report_product_exportLowstockCsv' => 'export',
            'reports_report_product_exportLowstockExcel' => 'export',
            'reports_report_product_exportDownloadsCsv' => 'export',
            'reports_report_product_exportDownloadsExcel' => 'export',
            'reports_report_customer_exportAccountsCsv' => 'export',
            'reports_report_customer_exportAccountsExcel' => 'export',
            'reports_report_customer_exportTotalsCsv' => 'export',
            'reports_report_customer_exportTotalsExcel' => 'export',
            'reports_report_customer_exportOrdersCsv' => 'export',
            'reports_report_customer_exportOrdersExcel' => 'export',
            'reports_report_review_exportCustomerCsv' => 'export',
            'reports_report_review_exportCustomerExcel' => 'export',
            'reports_report_review_exportProductCsv' => 'export',
            'reports_report_review_exportProductExcel' => 'export',
            'reports_report_statistics_refreshRecent' => 'freshRecent',
            'reports_index_exportSearchCsv' => 'export',
            'reports_index_exportSearchExcel' => 'export',
            'invitations_report_invitation_exportCsv' => 'export',
            'invitations_report_invitation_exportExcel' => 'export',
            'invitations_report_invitation_exportCustomerCsv' => 'export',
            'invitations_report_invitation_exportCustomerExcel' => 'export',
            'invitations_report_invitation_exportOrderCsv' => 'export',
            'invitations_report_invitation_exportOrderExcel' => 'export',
            'adminhtml_system_config_index' => 'view',
            'adminhtml_system_config_edit' => 'view',
            'adminhtml_system_config_save' => 'save',
            'catalog_rule_promo_catalog_edit' => 'view',
            'catalog_rule_promo_catalog_save' => 'save',
            'catalog_rule_promo_catalog_delete' => 'delete',
            'catalog_rule_promo_catalog_applyRules' => 'apply',
            'catalog_promo_quote_edit' => 'view',
            'sales_rule_promo_quote_save' => 'save',
            'sales_rule_promo_quote_delete' => 'delete',
            'adminhtml_system_account_index' => 'view',
            'adminhtml_system_account_save' => 'save',
            'newsletter_queue_edit' => 'view',
            'newsletter_queue_save' => 'save',
            'newsletter_template_save' => 'save',
            'newsletter_template_edit' => 'view',
            'newsletter_template_delete' => 'delete',
            'newsletter_template_preview' => 'view',
            'newsletter_subscriber_massUnsubscribe' => 'massUpdate',
            'newsletter_subscriber_massDelete' => 'massDelete',
            'newsletter_subscriber_exportCsv' => 'export',
            'newsletter_subscriber_exportXml' => 'export',
            'sales_order_pdfdocs' => 'export',
            'sales_order_view' => 'view',
            'sales_order_create_reorder' => 'reorder',
            'sales_order_edit_start' => 'edit',
            'sales_order_massHold' => 'massUpdate',
            'sales_order_massUnhold' => 'massUpdate',
            'sales_order_massCancel' => 'massUpdate',
            'sales_order_hold' => 'save',
            'sales_order_unhold' => 'save',
            'sales_order_cancel' => 'save',
            'sales_order_create_save' => 'save',
            'sales_order_edit_save' => 'save',
            'sales_order_email' => 'send',
            'sales_order_pdfinvoices' => 'export',
            'sales_order_pdfshipments' => 'export',
            'sales_order_pdfcreditmemos' => 'export',
            'sales_order_addComment' => 'save',
            'sales_invoice_view' => 'view',
            'sales_order_invoice_view' => 'view',
            'sales_order_invoice_save' => 'save',
            'sales_invoice_pdfinvoices' => 'export',
            'sales_order_invoice_print' => 'print',
            'sales_order_invoice_email' => 'email',
            'sales_shipment_view' => 'view',
            'sales_order_shipment_view' => 'view',
            'sales_order_shipment_save' => 'save',
            'sales_order_shipment_addComment' => 'save',
            'sales_shipment_pdfshipments' => 'export',
            'sales_order_shipment_print' => 'print',
            'sales_shipment_print' => 'print',
            'sales_order_shipment_email' => 'email',
            'sales_order_shipment_addTrack' => 'save',
            'sales_creditmemo_view' => 'view',
            'sales_order_creditmemo_view' => 'view',
            'sales_order_creditmemo_save' => 'save',
            'sales_order_creditmemo_addComment' => 'save',
            'sales_creditmemo_pdfcreditmemos' => 'export',
            'sales_order_creditmemo_print' => 'print',
            'sales_order_creditmemo_email' => 'email',
            'checkout_agreement_edit' => 'view',
            'checkout_agreement_save' => 'save',
            'checkout_agreement_delete' => 'delete',
            'adminhtml_user_role_editrole' => 'view',
            'adminhtml_user_role_saverole' => 'save',
            'adminhtml_user_role_delete' => 'delete',
            'adminhtml_user_edit' => 'view',
            'adminhtml_user_save' => 'save',
            'adminhtml_user_delete' => 'delete',
            'adminhtml_system_store_editWebsite' => 'view',
            'adminhtml_system_store_save' => 'save',
            'adminhtml_system_store_deleteWebsitePost' => 'delete',
            'adminhtml_system_store_editStore' => 'view',
            'adminhtml_system_store_deleteStorePost' => 'delete',
            'adminhtml_system_store_editGroup' => 'view',
            'adminhtml_system_store_deleteGroupPost' => 'delete',
            'adminhtml_system_design_save' => 'save',
            'adminhtml_system_design_delete' => 'delete',
            'adminhtml_system_currency_saveRates' => 'save',
            'adminhtml_email_template_save' => 'save',
            'adminhtml_email_template_edit' => 'view',
            'adminhtml_email_template_delete' => 'delete',
            'adminhtml_system_variable_save' => 'save',
            'adminhtml_system_variable_edit' => 'view',
            'adminhtml_system_variable_delete' => 'delete',
            'backup_index_create' => 'create',
            'backup_index_massDelete' => 'massDelete',
            'backup_index_rollback' => 'rollback',
            'tax_tax_ajaxDelete' => 'delete',
            'tax_tax_ajaxSave' => 'save',
            'tax_rule_edit' => 'view',
            'tax_rule_save' => 'save',
            'tax_rule_delete' => 'delete',
            'tax_rate_edit' => 'view',
            'tax_rate_save' => 'save',
            'tax_rate_importPost' => 'import',
            'tax_rate_exportPost' => 'export',
            'tax_rate_delete' => 'delete',
            'adminhtml_sitemap_edit' => 'view',
            'adminhtml_sitemap_save' => 'save',
            'adminhtml_sitemap_delete' => 'delete',
            'adminhtml_sitemap_generate' => 'save',
            'adminhtml_widget_instance_edit' => 'view',
            'adminhtml_widget_instance_save' => 'save',
            'adminhtml_widget_instance_delete' => 'delete',
            'adminhtml_cache_massEnable' => 'save',
            'adminhtml_cache_massDisable' => 'save',
            'adminhtml_cache_massRefresh' => 'save',
            'adminhtml_cache_cleanImages' => 'clean',
            'adminhtml_cache_cleanMedia' => 'clean',
            'adminhtml_cache_cleanStaticFiles' => 'clean',
            'adminhtml_cache_flushSystem' => 'flush',
            'adminhtml_cache_flushAll' => 'flush',
            'adminhtml_paypal_reports_details' => 'view',
            'adminhtml_paypal_reports_fetch' => 'fetch'
        ];
    }
}
