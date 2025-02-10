<?php

namespace Meetanshi\ImageClean\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Meetanshi\ImageClean\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Meetanshi\ImageClean\Model\ImagecleanFactory;
use Meetanshi\ImageClean\Model\Imageclean;

class CleanProductUseImage extends Command
{
    protected $helper;
    protected $productFactory;
    protected $productRepository;
    protected $imageCleanFactory;
    protected $imageClean;

    public function __construct(
        Data $data,
        ImagecleanFactory $imagecleanFactory,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Imageclean $imageclean
    )
    {
        $this->helper = $data;
        $this->imageCleanFactory = $imagecleanFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->imageClean = $imageclean;
        parent::__construct($name = null);
    }

    protected function configure()
    {
        $this->setName('imageclean:product-used-image:clean');
        $this->setDescription('Clear Product Used Image(s)');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helper->clearUsedImages();
        $output->writeln("Product's Used Image(s) was successfully cleaned");
    }
}