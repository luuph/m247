<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Ui\Component\Listing\Columns;

class RequestLink extends AbstractLink
{
    public const URL = 'amrma/request/view';
    public const ID_FIELD_NAME = 'request_id';
    public const ID_PARAM_NAME = 'request_id';
}
