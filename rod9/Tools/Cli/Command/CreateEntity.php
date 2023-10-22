<?php

namespace rod9\Tools\Cli\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use XF\Util\File;


class CreateEntity extends Command
{

    private function getInstallStep($addonID) {
        $methods = get_class_methods($addonID.'\Setup');
        $matches = preg_grep('/^installStep/m', $methods);
        return count($matches) + 1;
    }

    protected function configure()
    {
        $this
            ->setName('tools:create-entity')
            ->setDescription('Creates an entity and setup class for it');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question("<question>Enter add-on ID:</question> ");
        $addOnId = $helper->ask($input, $output, $question);
        $addOnId1 = str_replace('/', '\\', $addOnId);
        $dir = \XF::getAddOnDirectory();
        if(!is_dir($dir.'/'.$addOnId.'/Entity')) {
            FIle::createDirectory($dir.'/'.$addOnId.'/Entity', false);
        }
        $question = new Question("<question>Enter entity name:</question> ");
        $entityname = $helper->ask($input, $output, $question);
        $tablename = strtolower( explode('/', $addOnId)[0] . '_' . $entityname );
        $fp = fopen($dir.'/'.$addOnId.'/Setup.php', 'r+');
        $step = $this->getInstallStep($addOnId1);
        fseek($fp, '-1', SEEK_END);
        fwrite($fp, <<<CONTENT
        public function installStep$step() {
            \$this->schemaManager()->createTable('$tablename', function(Create \$table)
            {
                // ...
            });
        }
        public function uninstallStep$step() {
            \$this->schemaManager()->dropTable($tablename);
        }
CONTENT);
        print($step);

        FIle::writeFile($dir.'/'.$addOnId.'/Entity/'.$entityname. '.php', <<<CONTENT
<?php
namespace $addOnId1\Entity;

use XF\Mvc\Entity;
use XF\Mvc\Entity\Structure;

class $entityname extends Entity {

    	public static function getStructure(Structure \$structure)
	    {
            \$structure->table = '$tablename';
            \$structure->shortName = '$addOnId:$entityname';
            \$structure->primaryKey = 'REPLACE ME';
            \$structure->columns = [

            ];
		}

}

CONTENT
            , false);
        $output->writeln('Done.');
        return 0;
    }
}