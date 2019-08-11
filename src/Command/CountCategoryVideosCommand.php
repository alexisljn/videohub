<?php

namespace App\Command;

use App\Manager\CategoryManager;
use App\Manager\VideoManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CountCategoryVideosCommand extends Command
{
    protected static $defaultName = 'app:count-category-videos';
    private $categoryManager;
    private $videoManager;

    public function __construct(CategoryManager $categoryManager, VideoManager $videoManager)
    {
        $this->categoryManager = $categoryManager;
        $this->videoManager = $videoManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Count videos for a given category')
            ->addArgument('category', InputArgument::REQUIRED, 'Category filter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $category = $input->getArgument('category');
        $category = $this->categoryManager->getCategory($category);

        if($category) {
            $count = $this->videoManager->countVideosByCategory($category);
            $io->success('Category '.$category->getTitle().' has '.$count.' videos');
        } else {
            $io->error('This category doesn\'t exists');
        }


    }
}
