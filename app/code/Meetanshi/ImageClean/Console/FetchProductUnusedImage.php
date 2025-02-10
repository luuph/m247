<?php
namespace Meetanshi\ImageClean\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Meetanshi\ImageClean\Helper\Data;

class FetchProductUnusedImage extends Command
{
    protected $helper;

    public function __construct(Data $data)
    {
        $this->helper = $data;
        parent::__construct($name = null);
    }

    protected function configure()
    {
        $this->setName('imageclean:product-unused-image:fetch');
        $this->setDescription('ImageClean Fetch Product Image');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helper->compareList('unused');
        $output->writeln("Product Image(s) Successfully Fetched");
    }
}