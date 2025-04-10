<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Ui\Component\Listing\Columns\Profile;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magezon\Core\Helper\Data as CoreHelper;
use Magezon\LookBook\Helper\Data as DataHelper;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const IMAGE_URL = 'Magezon_LookBook::images/thumbnail.jpeg';
    const ALT_FIELD = 'name';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        CoreHelper $coreHelper,
        DataHelper $dataHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->coreHelper = $coreHelper;
        $this->dataHelper   = $dataHelper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $filename                           = $item['image'];
                if ($filename != '') {
                    $item[$fieldName . '_src']      = $this->coreHelper->getMediaUrl() . $filename;
                    $item[$fieldName . '_alt']      = $this->getAlt($item) ?: $filename;
                    $item[$fieldName . '_orig_src'] = $this->coreHelper->getMediaUrl() . $filename;
                } else {
                    $item[$fieldName . '_src']      = $this->dataHelper->getViewImageUrl(self::IMAGE_URL);
                    $item[$fieldName . '_alt']      = 'Default';
                    $item[$fieldName . '_orig_src'] = $this->dataHelper->getViewImageUrl(self::IMAGE_URL);
                }

                $item[$fieldName . '_link']         = $this->urlBuilder->getUrl(
                                                        'lookbook/profile/edit',
                                                        [
                                                            'profile_id' => $item['profile_id']
                                                        ]
                                                    );
            }
        }

        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
