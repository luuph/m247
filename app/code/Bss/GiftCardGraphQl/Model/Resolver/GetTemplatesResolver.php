<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Api\Data\TemplateSearchResultsInterface;
use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\Template\ImageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class GetTemplatesResolver implements ResolverInterface
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var ImageFactory
     */
    private $imageModel;

    /**
     * GetTemplatesResolver constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param ImageFactory $imageModel
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        ImageFactory $imageModel,
        TemplateRepositoryInterface $templateRepository
    ) {
        $this->templateRepository = $templateRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->imageModel = $imageModel;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $templates = $this->templateRepository->getList(
            $this->criteriaBuilder->create()
        );
        return $this->mappedTemplates($templates);
    }

    /**
     * Mapped data for graphql schema
     *
     * @param TemplateSearchResultsInterface $templateSearchedData
     *
     * @return array
     * @throws LocalizedException
     */
    protected function mappedTemplates($templateSearchedData)
    {
        try {
            $templates = [];
            foreach ($templateSearchedData->getItems() as $item) {
                $templateData = $item->getData();
                $templateData['images'] = $this->imageModel->create()->loadByTemplate($item->getId());
                $templates[] = $templateData;
            }
            return $templates;
        } catch (\Exception $e) {
            throw new LocalizedException(__("Couldn't get templates"));
        }
    }
}
