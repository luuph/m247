<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class Ar extends AbstractModifier
{
    /**
     * ArrayManager variable
     *
     * @var Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * Construct function
     *
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * ModifyMeta function
     *
     * @param array $meta
     * @return void
     */
    public function modifyMeta(array $meta)
    {
        $fieldCodes = ["ar_model_file_android", "ar_model_file_ios"];
        foreach ($fieldCodes as $fieldCode) {
            $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, "children");
            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . $fieldCode,
                $meta,
                null,
                "children"
            );
            if (!$elementPath) {
                return $meta;
            }
            $meta = $this->arrayManager->merge(
                $containerPath,
                $meta,
                [
                    "children" => [
                        $fieldCode => [
                            "arguments" => [
                                "data" => [
                                    "config" => [
                                        "elementTmpl" => "Webkul_MobikulCore/grid/filters/elements/ar",
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            );
        }
        $elementPath = $this->arrayManager->findPath("ar_2d_file", $meta, null, "children");
        $containerPath = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . "ar_2d_file",
            $meta,
            null,
            "children"
        );
        if (!$elementPath) {
            return $meta;
        }
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                "children" => [
                    "ar_2d_file" => [
                        "arguments" => [
                            "data" => [
                                "config" => [
                                    "elementTmpl" => "Webkul_MobikulCore/grid/filters/elements/arimage",
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
     * ModifyData function
     *
     * @param array $data
     * @return void
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
