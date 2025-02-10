<?php

namespace Meetanshi\ImageClean\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Meetanshi\ImageClean\Helper\Data;

class CleanCategoryImage extends Command
{
    protected $helper;

    public function __construct(Data $data)
    {
        $this->helper = $data;
        parent::__construct($name = null);
    }

    protected function configure()
    {
        $this->setName('imageclean:category-image:clean');
        $this->setDescription('Clean Category Image(s)');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helper->ClearUnusedImages('category');
        $output->writeln("Category Image(s) was successfully cleaned");
    }
}