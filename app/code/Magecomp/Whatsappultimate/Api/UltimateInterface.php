<?php
namespace Magecomp\Whatsappultimate\Api;

/**
 * Interface Whatsappultimate
 * Magecomp\Whatsappultimate\Api
 */
interface UltimateInterface
{
    /**
     * Send Reg Otp
     *
     * @param string $mobilenumber
     * @param string $countrycode
     * @param string $storeid
     * @param bool $isresend
     * @return string
     */
    public function sendRegotp(
        $mobilenumber,
        $countrycode,
        $isresend,
        $storeid 
    );

    /**
     * Verify Reg Otp
     *
     * @param string $mobilenumber
     * @param string $countrycode
     * @param string $otp
     * @param string $storeid
     * @return string
     */
    public function verifyRegotp(
        $mobilenumber,
        $countrycode,
        $otp,
        $storeid 
    );
    /**
     * Send Update Mobile Otp
     *
     * @param string $newmobilenumber
     * @param string $oldmobilenumber
     * @param string $countrycode
     * @param int $customer_id
     * @param string $storeid
     * @param bool $isresend
     * @return string
     */
    public function sendMobileUpdateOtp(
        $newmobilenumber,
        $oldmobilenumber,
        $countrycode,
        $customer_id,
        $isresend,
        $storeid
    );
    /**
     * Verify Update Mobile Otp
     *
     * @param string $newmobilenumber
     * @param string $oldmobilenumber
     * @param string $countrycode
     * @param int $customer_id
     * @param string $storeid
     * @param string $otp
     * @return string
     */
    public function verifyMobileUpdateOtp(
        $newmobilenumber,
        $oldmobilenumber,
        $countrycode,
        $customer_id,
        $otp,
        $storeid
    );
    
    /**
     * Send Registration Notification
     *
     * @param string $email
     * @param string $password
     * @param string $mobilenumber
     * @param string $countrycode
     * @param string $otp
     * @param int $storeId
     * @return string
     */
      public function sendRegistrationNotification(
        $email,
        $password,
        $mobilenumber,
        $countrycode,
        $otp,
        $storeId
    );
     /**
      * Send Order Notification
      *
      * @param int $orderid
      * @param bool $isresend
      * @return string
      */
    public function sendOrderNotification(
        $orderid,
        $isresend
    );

    /**
     * Send Invoice Notification
     *
     * @param int $invoiceid
     * @param bool $isresend
     * @return string
     */
    public function sendInvoiceNotification(
        $invoiceid,
        $isresend
    );

    /**
     * Send Shipment Notification
     *
     * @param int $shipmentid
     * @param bool $isresend
     * @return string
     */
    public function sendShipmentNotification(
        $shipmentid,
        $isresend
    );

    /**
     * Send Creditmemo Notification
     *
     * @param int $creditmemoid
     * @param bool $isresend
     * @return string
     */
    public function sendCreditmemoNotification(
        $creditmemoid,
        $isresend
    );

    /**
     * Send Contact Notification
     *
     * @param string $name
     * @param string $email
     * @param string $mobilenumber
     * @param string $comment
     * @param string $countrycode
     * @param int $storeId
     * @return string
     */
    public function sendContactNotification(
        $name,
        $email,
        $mobilenumber,
        $comment,
        $countrycode,
        $storeId
    );


    /**
     * Get Country List
     * @return string
     */
    public function getCountryList();
}
