<?php
namespace PivotX\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;

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
    /*
// add this to the top of your class

public function addEmailAction($email)
{
    $emailConstraint = new Email();
    // all constraint "options" can be set this way
    $emailConstraint->message = 'Invalid email address';

    // use the validator to validate the value
    $errorList = $this->get('validator')->validateValue($email, $emailConstraint);

    if (count($errorList) == 0) {
        // this IS a valid email address, do something
    } else {
        // this is *not* a valid email address
        $errorMessage = $errorList[0]->getMessage()

        // ... do something with the error
    }

    // ...
}
     */

    /**
     * Set-up PivotX users
     *
     * @return boolean  true, if setup successful
     */
    protected function setupUsers($input, $output, $doctrine)
    {
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

                        if (!$dialog->askConfirmation($output, 'Do you want me to upgrade this user? ', false)) {
                            $email = false;
                        }

                        // @todo maybe ask for new password? (that way we can fix hacked sites?)
                    }
                }
            }
            while ($email === false);

            if (($email === false) || ($email == '')) {
                $output->writeln('Set-up aborted.');
                return false;
            }

            $authLevel = 900;
            if (is_null($existingUser)) {
                $user = new \PivotX\CoreBundle\Entity\User;

                $new_password = $user->generatePassword('hard', 8);

                $factory  = $this->getContainer()->get('security.encoder_factory');
                $encoder  = $factory->getEncoder($user);
                $password = $encoder->encodePassword($new_password, $user->getSalt());

                $user->initNewCrudRecord();
                $user->setEnabled(true);
                $user->setLevel($authLevel);
                $user->setEmail($email);
                $user->setPasswd($password);

                $em->persist($user);

                $output->writeln('New password for user is: ' . $new_password);

                // @todo mail this?
            }
            else {
                if ($existingUser->getLevel() < $authLevel) {
                    $existingUser->setLevel($authLevel);
                }
                $existingUser->setEnabled(true);

                $em->persist($existingUser);

                $output->writeln('Existing user has been upgraded.');
            }

            $em->flush();
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $output->writeln('PivotX Setup');
        $output->writeln('');

        // fill translations
        // fill options
        // verify security (ROLES, see security.yml)
        // check if parameters.ini secret has been changed?

        if (!$this->setupUsers($input, $output, $doctrine)) {
            return;
        }

        $output->writeln('Setup has been verified. You are good to go!');
    }
}
