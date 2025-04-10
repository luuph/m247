<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIImageSearch
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AIImageSearch\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Helper\ProgressBar;
use Magento\Framework\Console\Cli;

class CreateProductEmbeddings extends Command
{
    public const PRODUCTID = 'Product ID';

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\AIImageSearch\Helper\Data $helper
     * @param \Magento\Framework\App\State $state
     * @param string $name
     */
    public function __construct(
        private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        private \Magento\Catalog\Model\ProductFactory $productFactory,
        private \Webkul\AIImageSearch\Helper\Data $helper,
        private \Magento\Framework\App\State $state,
        $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('generate:image:embeddings');
        $this->setDescription('Create product image embeddings for image search.');
        $options = [
            new InputOption(
                self::PRODUCTID,
                '-p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Product Ids'
            )
        ];
        $this->setDefinition($options);
        parent::configure();
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('frontend');
            $output->writeln("Please wait while embeddings get created.");
            $products = $this->productCollectionFactory->create()
                ->addAttributeToFilter('visibility', ['in' => [3, 4]]);
            if ($pids = $input->getOption(self::PRODUCTID)) {
                $pids = explode(",", $pids[0]);
                $products->addFieldToFilter('entity_id', ['in' => $pids]);
            }
            $totalProducts = $products->getSize();
            $output->writeln("Total Product: " . $totalProducts);
            $progressBar = new ProgressBar($output, $totalProducts);
            $progressBar->start();
            foreach ($products->getData() as $product) {
                $product = $this->productFactory->create()->load($product['entity_id']);
                $productId = $product->getEntityId();
                $collectionName = $this->helper::COLLECTION_NAME;
                $adapter = $this->helper->getAdapter();
                $adapter->intializeClients();
                $adapter->createCollection($collectionName);
                $collection = $adapter->getCollection($collectionName);
                $collectionId = $collection['id'];
                $adapter->deleteCollectionItems(
                    $collectionId,
                    [
                        'where' => [
                            'product_id' => $productId
                        ]
                    ]
                );
                if ($product->getTypeId() == 'configurable') {
                    $this->helper->generateProductImageEmbeddings(
                        $product,
                        $adapter,
                        $collectionId,
                        $productId,
                        1
                    );
                    $productTypeInstance = $product->getTypeInstance();
                    $usedProducts = $productTypeInstance->getUsedProducts($product);
                    foreach ($usedProducts as $childProduct) {
                        $this->helper->generateProductImageEmbeddings(
                            $childProduct,
                            $adapter,
                            $collectionId,
                            $productId,
                            0
                        );
                    }
                } else {
                    $this->helper->generateProductImageEmbeddings(
                        $product,
                        $adapter,
                        $collectionId,
                        $productId,
                        0
                    );
                }
                $progressBar->advance();
            }
            $progressBar->finish();
            $output->writeln("");
            $output->writeln("Embeddings created successfully.");
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
