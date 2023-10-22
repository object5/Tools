<?php

namespace rod9\Tools\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use XF\Util\File;

class CreateController extends Command
{
     protected function configure()
     {
         $this
             ->setName('tools:create-controller')
             ->setDescription('Creates a controller')
             ->addArgument(
                 'type',
                 InputArgument::REQUIRED,
                 'Type of controller (Pub/Admin/Api)'
             );
     }

     protected function execute(InputInterface $input, OutputInterface $output)
     {
         $helper = $this->getHelper('question');
         $question = new Question("<question>Enter add-on ID:</question> ");
         $addOnId = $helper->ask($input, $output, $question);
         $question = new Question("<question>Enter controller name:</question> ");
         $controllerName = $helper->ask($input, $output, $question);
         $type = $input->getArgument('type');
         $namespace = str_replace('/', '\\', $addOnId).'\\'.$type.'\Controller';
         $controllersDir = \XF::getAddOnDirectory().'/'.$addOnId.'/'.$type.'/Controller';
         if(!is_dir($controllersDir)) {
            File::createDirectory($controllersDir, false);
         }
        File::writeFile($controllersDir.'/'.$controllerName.'.php', <<<CONTENT
<?php
namespace $namespace;

use XF\\$type\\Controller\AbstractController;


class $controllerName extends AbstractController
{
    public function actionIndex()
    {
        // ....
    }
    
}
CONTENT, false);
         $output->writeln('Done.');
     }
}