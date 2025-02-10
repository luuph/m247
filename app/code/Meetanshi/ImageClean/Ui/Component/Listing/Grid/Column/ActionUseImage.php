<?php

namespace Meetanshi\ImageClean\Ui\Component\Listing\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ActionUseImage extends Column
{
    const ROW_DELETE_URL = 'imageclean/useimages/delete';

    protected $urlBuilder;
    protected $modelImagecleanFactory;
    private $deleteUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $deleteUrl = self::ROW_DELETE_URL
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->deleteUrl = $deleteUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['imageclean_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $this->deleteUrl,
                            ['imageclean_id' => $item['imageclean_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Image'),
                            'message' => __('Are you sure?')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}