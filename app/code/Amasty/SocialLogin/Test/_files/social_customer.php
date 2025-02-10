<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

use Amasty\SocialLogin\Api\Data\SocialInterfaceFactory;
use Amasty\SocialLogin\Api\Data\SocialInterface;
use Amasty\SocialLogin\Model\Repository\SocialRepository;
use Amasty\SocialLogin\Model\SocialList;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SocialInterfaceFactory $modelFactory */
$modelFactory = Bootstrap::getObjectManager()->get(SocialInterfaceFactory::class);
/** @var SocialRepository $repository */
$repository = Bootstrap::getObjectManager()->get(SocialRepository::class);

/** @var SocialInterface $model */
$model = $modelFactory->create();
$model->setType(SocialList::TYPE_FACEBOOK);
$model->setSocialId('123');
$model->setCustomerId(1);
$model->setName('Test');
$repository->save($model);
