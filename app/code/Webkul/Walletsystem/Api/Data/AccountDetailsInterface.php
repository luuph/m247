<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Api\Data;

/**
 * Webkul Walletsystem Account Details Interface
 */
interface AccountDetailsInterface
{
    public const  ENTITY_ID = 'entity_id';
    
    public const CUSTOMER_ID = 'customer_id';

    public const REQUEST_TO_DELETE = 'request_to_delete';

    public const HOLDERNAME = 'holdername';

    public const ACCOUNTNO = 'accountno';

    public const BANKNAME = 'bankname';

    public const BANKCODE = 'bankcode';

    public const ADDITIONAL = 'additional';
    
    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setEntityId($id);

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer ID
     *
     * @param int $customer_id
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get Request To Delete
     *
     * @return int|null
     */
    public function getRequestToDelete();

    /**
     * Set Request To Delete
     *
     * @param int $request_to_delete
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setRequestToDelete($request_to_delete);

    /**
     * Get Holder Name
     *
     * @return string|null
     */
    public function getHolderName();

    /**
     * Set Holder Name
     *
     * @param string $holdername
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setHolderName($holdername);

    /**
     * Get Account Number
     *
     * @return int|null
     */
    public function getAccountNo();

    /**
     * Set Account Number
     *
     * @param int $accountno
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setAccountNo($accountno);

    /**
     * Get Bank Name
     *
     * @return string|null
     */
    public function getBankName();

    /**
     * Set Bank Name
     *
     * @param string $bankname
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setBankName($bankname);

    /**
     * Get Bank Code
     *
     * @return string|null
     */
    public function getBankCode();

    /**
     * Set Bank Code
     *
     * @param string $bankcode
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setBankCode($bankcode);

    /**
     * Get Additional
     *
     * @return string|null
     */
    public function getAdditional();

    /**
     * Set Additional
     *
     * @param string $additional
     * @return \Webkul\Walletsystem\Api\Data\AccountDetailsInterface
     */
    public function setAdditional($additional);
}
