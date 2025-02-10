<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Test\Integration\Traits;

trait XmlFormatterTrait
{
    /**
     * Gets XML document by provided path.
     *
     * @param string $filePath
     * @return \stdClass
     */
    private function getXmlResponseData(string $filePath): \stdClass
    {
        $data = file_get_contents($filePath);
        $xml = new \SimpleXMLElement($data);

        $data = json_decode(json_encode($xml));

        if (isset($data->CompletedShipmentDetail)) { //for label response
            $data->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image =
                base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=');//black square
        }

        return $data;
    }
}
