<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.7.35
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



declare(strict_types=1);

namespace Mirasvit\Brand\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Model\Config\BrandPageConfig;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class BrandPage extends AbstractModel implements BrandPageInterface
{
    private $imageUploader;

    private $filesystem;

    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Context $context,
        Registry $registry,
        ImageUploader $imageUploader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->storeManager  = $storeManager;
        $this->imageUploader = $imageUploader;
        $this->filesystem    = $filesystem;
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttributeOptionId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_OPTION_ID);
    }

    public function setAttributeOptionId(int $value): BrandPageInterface
    {
        return $this->setData(self::ATTRIBUTE_OPTION_ID, $value);
    }

    public function getAttributeId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_ID);
    }

    public function setAttributeId(int $value): BrandPageInterface
    {
        return $this->setData(self::ATTRIBUTE_ID, $value);
    }

    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): BrandPageInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getLogo(): string
    {
        return (string)$this->getData(self::LOGO);
    }

    public function setLogo(string $value): BrandPageInterface
    {
        return $this->setData(self::LOGO, $value);
    }

    public function getBrandTitle(): string
    {
        return (string)$this->getDataFromGroupedField(self::BRAND_TITLE);
    }

    public function setBrandTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_TITLE, $value);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getData(self::URL_KEY);
    }

    public function setUrlKey(string $value): BrandPageInterface
    {
        return $this->setData(self::URL_KEY, $value);
    }

    public function getBrandDescription(): string
    {
        return (string)$this->getDataFromGroupedField(self::BRAND_DESCRIPTION);
    }

    public function setBrandDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_DESCRIPTION, $value);
    }

    public function getMetaTitle(): string
    {
        return (string)$this->getDataFromGroupedField(self::META_TITLE, 'meta_data');
    }

    public function setMetaTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::META_TITLE, $value);
    }

    public function getKeyword(): string
    {
        return (string)$this->getDataFromGroupedField(self::KEYWORD, 'meta_data');
    }

    public function setKeyword(string $value): BrandPageInterface
    {
        return $this->setData(self::KEYWORD, $value);
    }

    public function getMetaDescription(): string
    {
        return (string)$this->getDataFromGroupedField(self::META_DESCRIPTION, 'meta_data');
    }

    public function setMetaDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    public function getSeoDescription(): string
    {
        return (string)$this->getDataFromGroupedField(self::SEO_DESCRIPTION, 'meta_data');
    }

    public function setSeoDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::SEO_DESCRIPTION, $value);
    }

    public function getSeoPosition(): string
    {
        $seoPosition = (string)$this->getDataFromGroupedField(self::SEO_POSITION, 'meta_data');

        return $seoPosition ? (string)$seoPosition : BrandPageConfig::FROM_DEFAULT_POSITION;
    }

    public function setSeoPosition(string $value): BrandPageInterface
    {
        return $this->setData(self::SEO_POSITION, $value);
    }

    public function getRobots(): string
    {
        $robots = (string)$this->getDataFromGroupedField(self::ROBOTS, 'meta_data');

        return $robots ? (string)$robots : BrandPageConfig::INDEX_FOLLOW;
    }

    public function setRobots(string $value): BrandPageInterface
    {
        return $this->setData(self::ROBOTS, $value);
    }

    public function getCanonical(): string
    {
        return (string)$this->getDataFromGroupedField(self::CANONICAL, 'meta_data');
    }

    public function setCanonical(string $value): BrandPageInterface
    {
        return $this->setData(self::CANONICAL, $value);
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function getBrandName(): string
    {
        return (string)$this->getData(self::BRAND_NAME);
    }

    public function setBrandName(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_NAME, $value);
    }

    public function getBannerAlt(): string
    {
        return (string)$this->getData(self::BANNER_ALT);
    }

    public function setBannerAlt(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_ALT, $value);
    }

    public function getBannerTitle(): string
    {
        return (string)$this->getData(self::BANNER_TITLE);
    }

    public function setBannerTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_TITLE, $value);
    }

    public function getBanner(): string
    {
        return (string)$this->getData(self::BANNER);
    }

    public function setBanner(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER, $value);
    }

    public function getBannerPosition(): string
    {
        return (string)$this->getData(self::BANNER_POSITION);
    }

    public function setBannerPosition(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_POSITION, $value);
    }

    public function getBrandShortDescription(): string
    {
        return (string)$this->getDataFromGroupedField(self::BRAND_SHORT_DESCRIPTION);
    }

    public function setBrandShortDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_SHORT_DESCRIPTION, $value);
    }

    public function afterSave(): self
    {
        $logo = $this->getLogo();
        $this->moveFileFromTmp($logo);
        $banner = $this->getBanner();
        $this->moveFileFromTmp($banner);

        return parent::afterSave();
    }

    public function getBrandDisplayMode(): string
    {
        $displayMode = $this->getDataFromGroupedField(BrandPageStoreInterface::BRAND_DISPLAY_MODE);

        return $displayMode ? (string)$displayMode : Category::DM_PRODUCT;
    }

    public function getBrandCmsBlock(): ?string
    {
        if ($this->getBrandDisplayMode() == Category::DM_PRODUCT) {
            return null;
        }

        return $this->getDataFromGroupedField(BrandPageStoreInterface::BRAND_CMS_BLOCK)
            ? (string)$this->getDataFromGroupedField(BrandPageStoreInterface::BRAND_CMS_BLOCK)
            : null;
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\BrandPage::class);
    }

    private function moveFileFromTmp(string $image): void
    {
        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (
            $image && !$mediaDir->isExist($this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $image))
        ) {
            $this->imageUploader->moveFileFromTmp($image, true);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getDataFromGroupedField(string $key = null, string $fieldName = 'content', int $storeId = null): string
    {
        $fieldData = '';
        $store       = is_null($storeId) ? $this->storeManager->getStore()->getId() : $storeId;

        if (!$this->getData($fieldName) && $this->getId()) {
            $this->load($this->getId());
        }

        $fieldDataArray = $this->getData($fieldName);

        if (!$fieldDataArray || !$key) {
            return $fieldData;
        }

        if (
            $store
            && isset($fieldDataArray[$store])
            && isset($fieldDataArray[$store][$key])
            && trim($fieldDataArray[$store][$key])
            && (trim($fieldDataArray[$store][$key]) != BrandPageConfig::FROM_DEFAULT_POSITION)
        ) {
            $fieldData = trim($fieldDataArray[$store][$key]);
        } elseif (isset($fieldDataArray[0]) && isset($fieldDataArray[0][$key])) {
            $fieldData = trim($fieldDataArray[0][$key]);
        }

        if ($fieldData == BrandPageConfig::DISABLED_POSITION) {
            $fieldData = '';
        }

        return $fieldData;
    }
}
