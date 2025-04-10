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

namespace Magezon\LookBook\Block\Adminhtml\Category;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;
use Magezon\LookBook\Block\Adminhtml\Category\Tab\Profile;
use Magezon\LookBook\Model\Category;

class AssignProfiles extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Magezon_Core::assign_items.phtml';

    /**
     * @var Profile
     */
    protected $blockGrid;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context          $context
     * @param EncoderInterface $jsonEncoder
     * @param Registry         $registry
     * @param array            $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Registry $registry,
        array $data = []
    ) {
        $this->registry    = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Profile::class,
                'category.profile.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getJson()
    {
        $profiles = $this->getCategory()->getProfilesPosition();
        if (!empty($profiles)) {
            return $this->jsonEncoder->encode($profiles);
        }
        return '{}';
    }

    /**
     * Retrieve current category instance
     *
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        return 'category_profiles';
    }

    /**
     * @return string
     */
    public function getFormPart()
    {
        return 'lookbook_category_form';
    }

    /**
     * @return string
     */
    public function getAjaxParam()
    {
        return 'selected_profiles';
    }
}
