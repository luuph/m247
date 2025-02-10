<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Test\Integration\Model\ResourceModel;

use Amasty\SocialLogin\Model\ResourceModel\Social;
use Amasty\SocialLogin\Model\SocialList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\SocialLogin\Model\ResourceModel\Social
 * @magentoAppArea frontend
 * @magentoAppIsolation disabled
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoDataFixture Amasty_SocialLogin::Test/_files/social_customer.php
 */
class SocialTest extends TestCase
{
    /**
     * @var Social
     */
    private $model;

    protected function setUp(): void
    {
        $manager = Bootstrap::getObjectManager();
        $this->model = $manager->get(\Amasty\SocialLogin\Model\ResourceModel\Social::class);
    }

    /**
     * @covers \Amasty\SocialLogin\Model\ResourceModel\Social::getTypeBySocialId
     * @dataProvider socialIdDataProvider
     * @param string $socialId
     * @param string|null $expectedResult
     */
    public function testGetTypeBySocialId($socialId, $expectedResult): void
    {
        $result = $this->model->getTypeBySocialId($socialId);
        self::assertSame($expectedResult, $result);
    }

    public function socialIdDataProvider(): array
    {
        return [
            'null result' => [
                '',
                null
            ],
            'simple result' => [
                '123',
                SocialList::TYPE_FACEBOOK
            ]
        ];
    }
}
