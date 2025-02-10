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
 * @package   mirasvit/module-landing-page
 * @version   1.0.13
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LandingPage\Block\Landing;

use Magento\Cms\Block\Block;
use Magento\Framework\View\Element\Template;
use Mirasvit\LandingPage\Api\Data\PageInterface;
use Mirasvit\LandingPage\Repository\PageRepository;
use Magento\Cms\Model\Template\FilterProvider;

class View extends Template
{

    private $pageRepository;

    private   $filterProvider;

    public function __construct(
        PageRepository   $pageRepository,
        FilterProvider   $filterProvider,
        Template\Context $context,
        array            $data = []
    ) {
        $this->pageRepository = $pageRepository;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function getTopBlock(): string
    {
        $html = '';

        if ($this->getLandingPage() && $this->getLandingPage()->getTopBlock() != 0) {
            $html = $this->getLayout()->createBlock(
                Block::class
            )->setBlockId(
                $this->getLandingPage()->getTopBlock()
            )->toHtml();
        }

        return $html;
    }

    public function getBottomBlock(): string
    {
        $html = '';

        if ($this->getLandingPage() && $this->getLandingPage()->getBottomBlock() != 0) {
            $html = $this->getLayout()->createBlock(
                Block::class
            )->setBlockId(
                $this->getLandingPage()->getBottomBlock()
            )->toHtml();
        }

        return $html;
    }

    public function getDescription(): ?string
    {
        $landingPage = $this->getLandingPage();

        if ($landingPage && $landingPage->getDescription()) {
            return $landingPage->getDescription();
        }

        return null;
    }

    public function getLandingPage(): ?PageInterface
    {
        $page   = null;
        $pageId = $this->getRequest()->getParam('landing');

        if ($pageId) {
            $page = $this->pageRepository->get((int)$pageId);
        }

        return $page;
    }

    public function filterOutputHtml($string): string
    {
        return $this->filterProvider->getPageFilter()->filter($string);
    }
}
