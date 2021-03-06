<?php
namespace PivotX\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use PivotX\Doctrine\Generator\Entities;
use PivotX\Doctrine\Generator\SoftEntity;
use PivotX\Doctrine\Generator\EntitiesRepresentation;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

class SetupCommand extends ContainerAwareCommand
{
    protected $drama = false;

    protected function configure()
    {
        $this
            ->setName('pivotx:setup')
            ->setDescription('Setup PivotX')
        ;
    }

    /**
     * Check install
     *
     * @todo this just checks if the autoloaded siteoptions are loaded
     */
    protected function checkInstall($input, $output, &$messages)
    {
        $siteoptions_service = $this->getContainer()->get('pivotx.siteoptions');

        if (!$siteoptions_service->isCacheInitted()) {
            $output->writeln('The site has not yet been properly installed.');
            $output->writeln('Configure the database, run the command below and run this setup again.');
            $output->writeln('');
            $output->writeln('php app/console doctrine:schema:update --force');
            $output->writeln('');
            $output->writeln('If you are seeing this message again it probably means the database has still not been created.');

            return false;
        }

        return true;
    }

    /**
     * Update siteoptions
     *
     * @return boolean  true, if update successful
     */
    protected function updateSiteoptions($input, $output, &$messages)
    {
        $siteoptions_service = $this->getContainer()->get('pivotx.siteoptions');

        $siteoptions_service->beginTrans();

        $setup = new \PivotX\Component\Siteoptions\Setup($siteoptions_service);
        $setup->updateAllOptions();

        $siteoptions_service->commitTrans();

        return true;
    }

    /**
     * Handle the dialog to add a new SUPER_ADMIN user
     */
    protected function addUser($output, $repository)
    {
        $existingUser = null;
        $email        = null;

        do {
            $dialog = $this->getHelperSet()->get('dialog');
            $email = $dialog->ask($output, 'Please enter e-mailaddress for the admin user: ', false);

            $emailConstraint = new Email();

            $errorList = $this->getContainer()->get('validator')->validateValue($email, $emailConstraint);

            if (count($errorList) > 0) {
                $email = false;

                $output->writeln($errorList[0]->getMessage());
            }
            else {
                $existingUser = $repository->findOneByEmail($email);
                if (!is_null($existingUser)) {
                    $output->writeln('E-mailaddress already exists.');

                    if (!$existingUser->getEnabled()) {
                        $output->writeln('User has been disabled, upgrading will mean the user gets enabled.');
                    }

                    $is_admin = false;
                    foreach($existingUser->getRoles() as $role) {
                        if ($role == 'ROLE_SUPER_ADMIN') {
                            $is_admin = true;
                        }
                    }
                    if (!$is_admin) {
                        $output->writeln('User level is below admin level, user will be upgraded.');
                    }

                    if (!$dialog->askConfirmation($output, 'Do you want me to upgrade this user? ', false)) {
                        $email = false;
                    }

                    // @later maybe ask for new password? (that way we can fix hacked sites?)
                }
            }
        }
        while ($email === false);

        return array($existingUser, $email);
    }

    /**
     * Set-up PivotX users
     *
     * @return boolean  true, if setup successful
     */
    protected function setupUsers($input, $output, &$messages)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $doctrine->getRepository('PivotX\CoreBundle\Entity\User');
        $em         = $doctrine->getEntityManager();


        $output->write('Verifying users...');
        if ($this->drama) {
            sleep(5);
        }

        $users = $repository->findAll();

        $add_user = false;
        $message  = '';

        if (count($users) === 0) {
            $add_user = true;
            $message  = 'No users present.';
        }
        else {
            $have_admin_user = false;
            foreach($users as $user) {
                if ($user->getEnabled()) {
                    $roles = $user->getRoles();
                    foreach($roles as $role) {
                        if ($role == 'ROLE_SUPER_ADMIN') {
                            $have_admin_user = true;
                        }
                    }
                }
            }
            if (!$have_admin_user) {
                $add_user = true;
                $message  = 'No user with sufficient privileges found.';
            }
        }

        $output->write("\r".$message.str_repeat(' ', 50-strlen($message))."\r");

        $existingUser = null;
        if ($add_user) {
            $output->writeln('');

            list($existingUser, $email) = $this->addUser($output, $repository);

            if (($email === false) || ($email == '')) {
                $output->writeln('Set-up aborted.');
                return false;
            }

            if (is_null($existingUser)) {
                $user = new \PivotX\CoreBundle\Entity\User;

                $new_password = $user->generatePassword('hard', 8);

                $factory = $this->getContainer()->get('security.encoder_factory');
                $user->setEncoderFactory_passwd($factory);

                $user->initNewCrudRecord();
                $user->setEnabled(true);
                $user->addRole('ROLE_SUPER_ADMIN');
                $user->setEmail($email);
                $user->setPasswd($new_password);
                $user->setSettings(array());

                $em->persist($user);

                $output->writeln('New password for user is: ' . $new_password);
            }
            else {
                $existingUser->addRole('ROLE_SUPER_ADMIN');
                $existingUser->setEnabled(true);

                $em->persist($existingUser);

                $output->writeln('Existing user has been upgraded.');
            }

            $em->flush();
        }

        return true;
    }

    /**
     * Read the defined entities from the PivotX configuration and
     * generate YAML entities and entity/repository code.
     */
    protected function updateSoftEntities($input, $output, &$messages)
    {
        $kernel      = $this->getApplication()->getKernel();
        $doctrine    = $this->getContainer()->get('doctrine');
        $siteoptions = $this->getContainer()->get('pivotx.siteoptions');

        $require_doctrine_update = false;

        $er = new EntitiesRepresentation();
        $er->importDoctrineConfiguration($doctrine, $kernel);
        $er->importPivotConfiguration($siteoptions);
        $entities = $er->getEntities();
        foreach($entities as $entity) {
            $soft_entity = new SoftEntity($entity, $kernel);

            if ($entity->getState() == 'deleted') {
                $siteoptions->clearSiteOptions('all', 'config.entities', $entity->getInternalName());
                if ($soft_entity->deleteYaml()) {
                    $require_doctrine_update = true;
                    // @todo this update won't actually delete the table
                }
            }
            else if ($entity->getManaged() != 'ignore') {
                if ($soft_entity->writeYaml()) {
                    $require_doctrine_update = true;
                }

                $soft_entity->writeEntityPhp(false);
                $soft_entity->writeRepositoryPhp(false);

                $soft_entity->markChanges();

                $json = json_encode($entity->exportPivotConfig());
                $siteoptions->set('config.entities.'.$entity->getInternalName(), $json, 'application/json', false, false, 'all');

                //
                // @todo the entity/repository php code has not been updated for the PivotX/Doctrine features
                //       for now, just run the setup again
            }
        }

        if ($require_doctrine_update) {
            /**
             * @todo
             * We do not check if the doctrine updates has been run, so for now you only get one
             * message. In the future we should perform a doctrine schema check.
             */
            $messages[] = 'Your Doctrine configuration has been updated. Run the following command:';
            $messages[] = '';
            $messages[] = 'php app/console doctrine:schema:update --force';
            $messages[] = '';
        }

        return true;
    }

    /**
     * Read all the defined entities (YAML-only currently) and update
     * the source entity files to have all the correct methods.
     *
     * @return boolean  true, if update successful
     */
    protected function updateHardEntities($input, $output, &$messages)
    {
        $kernel   = $this->getApplication()->getKernel();
        $doctrine = $this->getContainer()->get('doctrine');
        $translation_service = $this->getContainer()->get('pivotx.translations');

        $translation_service->beginTrans();

        $generator = new Entities($kernel, $doctrine, $translation_service);

        $generator->updateAllCode();
        $generator->updateAllTranslations();

        $translation_service->commitTrans();

        $siteoption_service = $this->getContainer()->get('pivotx.siteoptions');
        $siteoption_service->set('config.check.entities', 0, 'x-value/boolean', false, false, 'all');

        return true;
    }

    /**
     * Update config.check.any to the proper value.
     */
    protected function updateConfigCheck($input, $output, &$messages)
    {
        $siteoptions_service = $this->getContainer()->get('pivotx.siteoptions');

        $setup = new \PivotX\Component\Siteoptions\Setup($siteoptions_service);
        $setup->updateConfigCheck();

        return true;
    }

    /**
     * Execute PivotX Setup
     *
     * fill options
     * verify security (ROLES, see security.yml)
     * check if parameters.ini secret has been changed?
     *
     * run: pivotx:entities
     *      - implicitily add translations
     * run: doctrine:schema:update (--force)
     * run: generate:doctrine:entities if necessary
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('PivotX Setup');
        $output->writeln('');

        $messages = array();

        if (!$this->checkInstall($input, $output, $messages)) {
            // checkInstall how outputted exactly wat was wrong
            return;
        }

        if (!$this->updateSiteoptions($input, $output, $messages)) {
            $output->writeln('Setup aborted. Options could not be updated.');
            return;
        }

        if (!$this->setupUsers($input, $output, $messages)) {
            $output->writeln('Setup aborted. Users could not be verified.');
            return;
        }

        if (!$this->updateSoftEntities($input, $output, $messages)) {
            $output->writeln('Setup aborted. Soft-entities could not be updated.');
            return;
        }

        if (!$this->updateHardEntities($input, $output, $messages)) {
            $output->writeln('Setup aborted. Hard-entities could not be updated.');
            return;
        }

        if (!$this->updateConfigCheck($input, $output, $messages)) {
            $output->writeln('Setup aborted. Configuration check failed.');
            return;
        }

        if (count($messages) == 0) {
            $output->writeln('Setup has been verified. You are good to go!');
        }
        else {
            $output->writeln('Setup has been verified and the following actions should be performed:');
            $output->writeln('');

            foreach($messages as $message) {
                $output->writeln($message);
            }
        }
    }
}
