<?php
namespace Meetanshi\ImageClean\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Meetanshi\ImageClean\Helper\Data;

class FetchCategoryImage extends Command
{
    protected $helper;

    public function __construct(Data $data)
    {
        $this->helper = $data;
        parent::__construct($name = null);
    }

    protected function configure()
    {
        $this->setName('imageclean:category-image:fetch');
        $this->setDescription('ImageClean Fetch Category Image');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helper->compareCategoryList();
        $output->writeln("Unused Category Image(s) Successfully Fetched");
    }
}