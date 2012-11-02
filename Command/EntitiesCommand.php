<?php
namespace PivotX\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use PivotX\Doctrine\Generator\Entities;

class EntitiesCommand extends ContainerAwareCommand
{
    protected $drama = false;

    protected function configure()
    {
        $this
            ->setName('pivotx:entities')
            ->setDescription('Generate/update Entity files PivotX')
        ;
    }

    /**
     * Read all the defined entities (YAML-only currently) and update
     * the source entity files to have all the correct methods.
     */
    protected function updateEntities()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $translation_service = $this->getContainer()->get('pivotx.translations');

        $generator = new Entities($doctrine, $translation_service);

        $generator->updateAllCode();
        $generator->updateAllTranslations();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('PivotX Entities Update');
        $output->writeln('');

        $this->updateEntities();

        $output->writeln('Your entities have been updated.');
    }
}
