<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Test\Integration\Model;

use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Model\Request\Request;
use Amasty\RmaAutomaticShippingLabel\Model\CarriersAndMethodsProvider;
use Amasty\RmaAutomaticShippingLabel\Test\Integration\Traits\XmlFormatterTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CarriersAndMethodsProviderTest extends TestCase
{
    use XmlFormatterTrait;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CarriersAndMethodsProvider
     */
    private $provider;

    /**
     * @var ClientFactory|MockObject
     */
    private $clientFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->provider = $this->objectManager->create(CarriersAndMethodsProvider::class);
        $this->clientFactory = $this->createPartialMock(ClientFactory::class, ['create']);
        $this->objectManager->addSharedInstance($this->clientFactory, ClientFactory::class);
    }

    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(ClientFactory::class);
    }

    /**
     * @magentoDataFixture Amasty_RmaAutomaticShippingLabel::Test/Integration/_files/rma.php
     * @magentoConfigFixture admin_store carriers/fedex/active_amrma 1
     * @magentoConfigFixture current_store carriers/fedex/active_amrma 1
     * @magentoConfigFixture admin_store general/store_information/phone 321789821
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/address_source 2
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/country_id US
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/contact_name Test
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/city Los Angeles
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/street_line1 Street 1
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/postcode 11111
     * @magentoConfigFixture admin_store amrmashiplabel/return_address/region_id 12
     * @magentoConfigFixture admin_store carriers/fedex/allowed_methods FEDEX_GROUND
     * @magentoConfigFixture admin_store carriers/fedex/allowed_methods Fedex
     */
    public function testGetCarriersAndMethodsWithShippingLabels()
    {
        /** @var Request $rma */
        $rma = $this->objectManager->create(Request::class);
        $rma->load('RmaTestCustomer', RequestInterface::CUSTOMER_NAME);

        /** @var \SoapClient $client */
        $client = $this->createPartialMock(\SoapClient::class, ['getRates']);
        $this->clientFactory->method('create')
            ->willReturn($client);
        $client->method('getRates')
            ->with($this->callback(function ($request) {
                if (isset($request['RequestedShipment']['ServiceType'])
                    && $request['RequestedShipment']['ServiceType'] == 'SMART_POST'
                ) {
                    return false;
                }

                return true;
            }))
            ->willReturn($this->getXmlResponseData(__DIR__ . '/../_files/RatesResponse.xml'));

        $result = $this->provider->getCarriersAndMethodsWithShippingLabels($rma->getRequestId());
        $this->assertEquals(
            'fedex_FEDEX_GROUND',
            $result['fedex']['methods'][0]['code']
        );
    }
}
