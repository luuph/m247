<?php

namespace Biztech\Translator\Ui\Component\MassAction\Group;

use Magento\Framework\UrlInterface;
use Biztech\Translator\Helper\Language;

/**
 * OptionsCmsPage for mass action
 */
class OptionsCmsPage implements \JsonSerializable
{
    /**
     * options data.
     * @var array
     */
    protected $options;
    /**
     * Languages helper
     * @var \Biztech\Translator\Helper\Language
     */
    protected $languages;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var string
     */
    protected $urlPath;
    /**
     * @var string
     */
    protected $paramName;
    /**
     * @var array
     */
    protected $additionalData = [];
   
    /**
     * Options constructor.
     * @param Language $languages
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Language $languages,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->languages = $languages;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize() : ?array
    {
        if ($this->options === null) {
            $options = $this->languages->getLanguages();
            $this->prepareData();
            foreach ($options as $key => $optionCode) {
                $this->options[$key] = [
                    'type' => $key,
                    'label' => $optionCode,
                    'identifier' => 'massaction_cms_translate',
                    'identifier_url' => $this->urlBuilder->getUrl('translator/cron/check')
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$key]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $key]
                    );
                }

                $this->options[$key] = array_merge_recursive(
                    $this->options[$key],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
