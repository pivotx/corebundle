<?php
namespace PivotX\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;

class UpdateCommand extends ContainerAwareCommand
{
    protected $drama = false;

    protected function configure()
    {
        $this
            ->setName('pivotx:update')
            ->setDescription('Update PivotX')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $output->writeln('PivotX Update');
        $output->writeln('');

        // @todo do this
        // run: pivotx:entities
        //      - implicitily add translations
        // run: doctrine:schema:update (--force)
        // run: generate:doctrine:entities if necessary

        $output->writeln('Your configuration has been updated.');
    }
}
