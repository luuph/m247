<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class File extends AbstractModifier
{
    /**
     * @var Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * Initialize dependencies
     *
     * @param ArrayManager $arrayManager
     * @return void
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Return modified meta data
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $fieldCode = 'model_file';
        $elementPath = $this->arrayManager->findPath(
            $fieldCode,
            $meta,
            null,
            'children'
        );
        $containerPath = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . $fieldCode,
            $meta,
            null,
            'children'
        );

        if (!$elementPath) {
            return $meta;
        }

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children'  => [
                    $fieldCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'elementTmpl' => 'Webkul_WebAr/elements/file',
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        );
        return $meta;
    }

    /**
     * Return modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
