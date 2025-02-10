<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Test\Integration\Model\ShippingLabel;

use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Model\Request\Repository;
use Amasty\Rma\Model\Request\Request;
use Amasty\Rma\Utils\FileUpload;
use Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ShippingLabelManagement;
use Amasty\RmaAutomaticShippingLabel\Test\Integration\Traits\XmlFormatterTrait;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ShippingLabelManagementTest extends TestCase
{
    use XmlFormatterTrait;

    public const TEST_DATA = [
        'code' => 'fedex_FEDEX_GROUND',
        'carrier_title' => 'Federal Express',
        'method_title' => 'Ground',
        'price' => '13.43',
        'packages' =>
            [
                [
                    'params' =>
                        [
                            'container' => 'YOUR_PACKAGING',
                            'weight' => '15',
                            'customs_value' => '38',
                            'length' => '15',
                            'width' => '15',
                            'height' => '15',
                            'weight_units' => 'POUND',
                            'dimension_units' => 'INCH',
                            'content_type' => '',
                            'content_type_other' => '',
                            'delivery_confirmation' => 'NO_SIGNATURE_REQUIRED',
                        ],
                    'items' =>
                        [
                            [
                                'qty' => '1',
                                'customs_value' => '38',
                                'price' => '45.00',
                                'name' => 'Push It Messenger Bag',
                                'weight' => '15.0000',
                                'product_id' => '14',
                                'order_item_id' => '5',
                            ],
                        ],
                ],
            ],
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ShippingLabelManagement
     */
    private $shippingLabelManagement;

    /**
     * @var ClientFactory|MockObject
     */
    private $clientFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shippingLabelManagement = $this->objectManager->create(ShippingLabelManagement::class);
        $this->clientFactory = $this->createPartialMock(ClientFactory::class, ['create']);
        $this->objectManager->addSharedInstance($this->clientFactory, ClientFactory::class);
    }

    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(ClientFactory::class);
    }

    /**
     * @magentoConfigFixture admin_store carriers/fedex/active_amrma 1
     * @magentoConfigFixture admin_store general/store_information/phone 321789821
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/address_source 2
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/country_id US
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/contact_name Test
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/city Los Angeles
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/street_line1 Street 1
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/postcode 11111
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/region_id 12
     * @magentoDataFixture Amasty_RmaAutomaticShippingLabel::Test/Integration/_files/rma.php
     */
    public function testCreateShippingLabel()
    {
        /** @var \SoapClient $client */
        $client = $this->createPartialMock(\SoapClient::class, ['processShipment']);
        $this->clientFactory->method('create')
            ->willReturn($client);
        $client->method('processShipment')
            ->with($this->callback(function ($request) {
                $this->assertEquals(
                    'RECIPIENT',
                    $request['RequestedShipment']['ShippingChargesPayment']['PaymentType']
                );

                return true;
            }))
            ->willReturn($this->getXmlResponseData(__DIR__ . '/../../_files/LabelResponse.xml'));

        /** @var Request $rma */
        $rma = $this->objectManager->create(Request::class);
        $rma->load('RmaTestCustomer', RequestInterface::CUSTOMER_NAME);

        $this->shippingLabelManagement->createShippingLabel($rma->getRequestId(), self::TEST_DATA);

        $rma = $this->objectManager->create(Repository::class)->getById($rma->getId());
        $this->assertEquals(1, count($rma->getTrackingNumbers()));

        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->create(Filesystem::class);
        $mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(
            FileUpload::MEDIA_PATH . $rma->getId() . DIRECTORY_SEPARATOR . $rma->getShippingLabel()
        );
        $this->assertFileExists(
            $mediaPath
        );
    }
}
