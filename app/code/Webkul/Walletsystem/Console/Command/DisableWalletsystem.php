<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\PatchHistoryFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class DisableWalletsystem
 * Command to disable wallet system
 */
class DisableWalletsystem extends Command
{
    protected const PATCH_TABLE = \Webkul\Walletsystem\Setup\Patch\Data\CreateAttributes::class;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $setupFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $modStatus;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var PatchHistoryFactory
     */
    protected $patchHistoryFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $dataSetupFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Framework\Module\Status $modStatus
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\State $appstate
     * @param EavSetupFactory $eavSetupFactory
     * @param PatchHistoryFactory $patchHistoryFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $dataSetupFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appstate,
        EavSetupFactory $eavSetupFactory,
        PatchHistoryFactory $patchHistoryFactory
    ) {
        $this->setupFactory = $dataSetupFactory;
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->eavAttribute = $entityAttribute;
        $this->modStatus = $modStatus;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->appState = $appstate;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->patchHistoryFactory = $patchHistoryFactory;
        parent::__construct();
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('walletsystem:disable')
            ->setDescription('Walletsystem Disable Command');
        parent::configure();
    }

   /**
    * Execute
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->moduleManager->isEnabled('Webkul_Walletsystem')) {
            $connection = $this->resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $connection->dropForeignKey(
                $connection->getTableName('wk_ws_wallet_transaction_data'),
                $connection->getForeignKeyName(
                    'wk_ws_wallet_transaction_data',
                    'transaction_id',
                    'wk_ws_wallet_transaction',
                    'entity_id'
                )
            );
            // drop custom tables
            $connection->dropTable($connection->getTableName('wk_ws_credit_rules'));
            $connection->dropTable($connection->getTableName('wk_ws_credit_amount'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_record'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_transaction'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_payee'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_notification'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_account_details'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_transaction_data'));
            
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setupFactory]);
            // delete wallet_credit_based_on product attribute
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'wallet_credit_based_on');
            // delete wallet_cash_back product attribute
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'wallet_cash_back');
            // delete product
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            $this->registry->register('isSecureArea', true);
            // disable walletsystem module
            $this->modStatus->setIsEnabled(false, ['Webkul_Walletsystem']);

            // delete entry from setup_module table
            $setupModuleTable = $this->resource->getTableName('setup_module');
            $patchList = $this->resource->getTableName('patch_list');
            
            $connection->delete($setupModuleTable, "module = 'Webkul_Walletsystem'");
            /*delete patch from patch_list table because if we don't delete it the attribute will not create */
            $patchName = explode('::', self::PATCH_TABLE);

            $this->patchHistoryFactory->create()->revertPatchFromHistory($patchName[0]);
            $output->writeln('<info>Webkul Walletsystem module has been disabled successfully.</info>');
        }
    }
}
