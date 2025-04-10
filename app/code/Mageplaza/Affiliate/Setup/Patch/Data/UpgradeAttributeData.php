<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Affiliate\Setup\Patch\Data;

use Exception;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Stdlib\DateTime;
use Mageplaza\Affiliate\Model\Campaign\Display;
use Mageplaza\Affiliate\Model\Campaign\Status;

/**
 * Class UpgradeAttributeData
 *
 * @package Mageplaza\Affiliate\Setup\Patch\Data
 */
class UpgradeAttributeData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * CreateViewTable constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        $recordCount = $setup->getConnection()->fetchOne(
            $this->moduleDataSetup->getConnection()->select()
                ->from($this->moduleDataSetup->getTable('mageplaza_affiliate_group'), 'COUNT(*)')
        );
        if ($recordCount == 0) {
            $setup->getConnection()->insertMultiple(
                $setup->getTable('mageplaza_affiliate_group'),
                [
                    ['group_id' => '1', 'name' => 'General', 'created_at' => date('Y-m-d h:i:s')],
                    ['group_id' => '2', 'name' => 'Bronze', 'created_at' => date('Y-m-d h:i:s')],
                    ['group_id' => '3', 'name' => 'Silver', 'created_at' => date('Y-m-d h:i:s')],
                    ['group_id' => '4', 'name' => 'Gold', 'created_at' => date('Y-m-d h:i:s')],
                    ['group_id' => '5', 'name' => 'Platinum', 'created_at' => date('Y-m-d h:i:s')]
                ]
            );
        }

        $setup->getConnection()->insert(
            $setup->getTable('mageplaza_affiliate_campaign'),
            $this->getCampaignDefaultData()
        );

        $this->insertBlock($setup);

        $setup->endSetup();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getCampaignDefaultData()
    {
        return [
            'name' => 'Default Campaign',
            'description' => 'This is a sample campaign',
            'status' => Status::ENABLED,
            'website_ids' => '1',
            'affiliate_group_ids' => '1,2,3,4,5',
            'display' => Display::ALLOW_GUEST,
            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)
        ];
    }

    /**
     * @param $setup
     *
     * @return $this
     * @throws Exception
     */
    public function insertBlock($setup)
    {
        $blocks = $this->getDataBlock();
        $blockFactory = $this->blockFactory->create();
        foreach ($blocks as $block) {
            $setup->getConnection()->delete($setup->getTable('cms_block'), ['identifier = ?' => $block['identifier']]);
            $blockFactory->load($block['identifier'], 'identifier')->setData($block)->save();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDataBlock()
    {
        $homecontent = '<h3 style="font-weight: bold;">Welcome to our Affiliate Network!</h3>
<p style="margin-bottom: 20px;">We are really delighted to offer you to an easy and profitable Internet business strategy. It is known as the Affiliate Program. The most unique aspect of Affiliate is that you can still earn commissions without having any products, investment costs, or a personal website. Our training also does not require any commercial or technological background. Starting with Affiliate is a great place to start for newcomers.</p>
<h3 style="font-weight: bold;">How does it work?</h3>
<p style="margin-bottom: 20px;">When you come to Affiliate Program, you just create a new account totally freely for your work. Then you will use available email or text link to sale our website to whoever you want. Commission will be given to you if person you refer clicks on one of our website&rsquo;s links. After a successful purchase, you will receive commission.</p>
<h3 style="font-weight: bold;">Controlling your work directly!</h3>
<p style="margin-bottom: 20px;">Affiliate Program makes you more active in business. You can check your account balance and track directly own transaction anytime.</p>
<h3 style="font-weight: bold;">Our Campaigns</h3>
<p style="margin-bottom: 20px;">Pay per sale: You will receive (X) commission for each product item of the first purchase made using the affiliate referral link. You will receive (Y) commission for each product item of your subsequent purchases.</p>
<p style="margin-bottom: 20px;">Discount policy: A consumer who makes their first purchase using the affiliate referral link earns (Z) off for each product item. The buyer will not receive a discount on subsequent transactions. However, the affiliate account continues to get commissions from this customer\'s subsequent orders.</p>';
        $refercontent = '<div>Share more, earn more!</div>
  <h3>How it works:</h3>
  <ul style="margin-left: 15px; list-style-type: disc; margin-bottom: 20px;">
    <li>Create your referral link.</li>
    <li>Share our link to your friends by using our template.</li>
    <li>Paid commision per click, action and purchase.</li>
  </ul>';
        $termcontent = '<p>By filling out the signup form you acknowledge that you have read the terms and conditions, understand, and agree with them.</p>
<h3>Joining the Program</h3>
<p>By filling out the signup form, and upon acceptance, you will become an affiliate and are bound by the terms of this agreement. Your participation in the program is solely for this purpose: to legally advertise our website to receive a commission on products purchased by your referred individuals.</p>
<h3>Affiliate Responsibilities</h3>
<p>It is understood that you will introduce our products to your current and prospective customers and will comply with all laws as well those that govern email marketing and anti-spam laws. {{config path="general/store_information/name"}} reserves the right to accept or reject any prospective customers and will pay you a commission per customer referred using your affiliate code according to the designated payment schedule.</p>
<p>Either you or {{config path="general/store_information/name"}} may terminate the Affiliate relationship at any time. You are only eligible to earn Affiliate payments during your time as an approved Affiliate. {{config path="general/store_information/name"}} may change the program or service policies and operating procedures at any time.</p>
<h3>Affiliate Relationship</h3>
<p>This Affiliate relationship is one of independent contractors. {{config path="general/store_information/name"}} will not be liable for indirect, special or consequential damages arising in connection with this program and our aggregate liability arising with respect to this program will not exceed the total referral fees paid or payable to you. Interspire makes no express or implied warranties or representations with respect to the program. In addition, {{config path="general/store_information/name"}} makes no representation that the operation of the service will be uninterrupted or error-free, and {{config path="general/store_information/name"}} will not be liable for the consequences of any interruptions or errors.</p>';
        $policycontent = '<h3 style="font-weight: bold;">How does it work?</h3>
<p style="margin-bottom: 20px;">When you come to Affiliate Program, you just create a new account totally freely for your work. Then you will use available email or text link to sale our website to whoever you want. Commission will be given to you if person you refer clicks on one of our website&rsquo;s links. After a successful purchase, you will receive commission.</p>
<h3 style="font-weight: bold;">Controlling your work directly!</h3>
<p style="margin-bottom: 20px;">Affiliate Program makes you more active in business. You can check your account balance and track directly own transaction anytime.</p>
<h3 style="font-weight: bold;">Our Campaigns</h3>
<p style="margin-bottom: 20px;">Pay per sale: You will receive (X) commission for each product item of the first purchase made using the affiliate referral link. You will receive (Y) commission for each product item of your subsequent purchases.</p>
<p style="margin-bottom: 20px;">Discount policy: A consumer who makes their first purchase using the affiliate referral link earns (Z) off for each product item. The buyer will not receive a discount on subsequent transactions. However, the affiliate account continues to get commissions from this customer\'s subsequent orders.</p>';
        return [
            [
                'title' => __('Affiliate Welcome homepage content'),
                'identifier' => 'affiliate-home',
                'content' => $homecontent,
                'stores' => [0],
                'is_active' => 1
            ],
            [
                'title' => __('Affiliate referfriend description'),
                'identifier' => 'affiliate-referfriend-description',
                'content' => $refercontent,
                'stores' => [0],
                'is_active' => 1
            ],
            [
                'title' => __('Affiliate terms & conditions'),
                'identifier' => 'affiliate-term-condition',
                'content' => $termcontent,
                'stores' => [0],
                'is_active' => 1
            ],
            [
                'title' => __('Affiliate Policy Content'),
                'identifier' => 'affiliate-policy',
                'content' => $policycontent,
                'stores' => [0],
                'is_active' => 1
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
