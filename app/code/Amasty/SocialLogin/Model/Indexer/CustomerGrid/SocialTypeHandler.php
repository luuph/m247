<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\Indexer\CustomerGrid;

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\Indexer\HandlerInterface;

class SocialTypeHandler implements HandlerInterface
{
    /**
     * @var ColumnValueExpressionFactory
     */
    private $columnValueExpressionFactory;

    public function __construct(ColumnValueExpressionFactory $columnValueExpressionFactory)
    {
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
    }

    /**
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        $source->getSelect()->columns([$fieldInfo['name'] => $this->columnValueExpressionFactory->create([
            'expression' => sprintf('GROUP_CONCAT(DISTINCT %s.%s)', $alias, SocialInterface::TYPE)
        ])]);
        $source->getSelect()->group('e.' . $source->getIdFieldName());
    }
}
