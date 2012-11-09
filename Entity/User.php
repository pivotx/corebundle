<?php

namespace PivotX\CoreBundle\Entity;

use \Symfony\Component\Security\Core\User\UserInterface;

/**
 */
class User implements UserInterface
{
    private $id;
    private $date_created;
    private $date_modified;
    private $date_last_login;
    private $enabled;
    private $level;
    private $email;
    private $passwd_salt;
    private $passwd;
    private $activitylogs;

    /**
     */
    public function __construct()
    {
        $this->enabled     = true;
        $this->passwd_salt = md5(uniqid(null, true));

        $this->activitylogs = new \Doctrine\Common\Collections\ArrayCollection;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date_created
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;
    }

    /**
     * Get date_created
     *
     * @return datetime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date_modified
     *
     * @param datetime $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->date_modified = $dateModified;
    }

    /**
     * Get date_modified
     *
     * @return datetime 
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * Set date_last_login
     *
     * @param datetime $dateLastLogin
     */
    public function setDateLastLogin($dateLastLogin)
    {
        $this->date_last_login = $dateLastLogin;
    }

    /**
     * Get date_last_login
     *
     * @return datetime 
     */
    public function getDateLastLogin()
    {
        return $this->date_last_login;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set passwd salt
     *
     * @param string $passwd_salt
     */
    public function setPasswdSalt($passwd_salt)
    {
        $this->passwd_salt = $passwd_salt;
    }

    /**
     * Get passwd salt
     *
     * @return string 
     */
    public function getPasswdSalt()
    {
        return $this->passwd_salt;
    }

    /**
     * Set passwd
     *
     * @param string $passwd
     */
    public function setPasswd($passwd)
    {
        if ($passwd != '') {
            $encoder = $this->encoder_factory_passwd->getEncoder(get_class($this));
            $this->passwd = $encoder->encodePassword($passwd, $this->getSalt());
        }
    }

    /**
     * Set the password
     */
    public function generatePassword($chars = false, $length = 8)
    {
        $characters = '';
        switch ($chars) {
            case 'hard':
                $characters .= '!@#$^&*()-_=+';
            case 'medium':
                $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            case 'easy':
                $characters .= 'abcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }

        if ($characters == '') {
            return $this->generatePasswd('hard', $length);
        }
        if ($length < 3) {
            $length = 3;
        }

        $password = '';
        for($i=0; $i < $length; $i++) {
            $password .= $characters[mt_rand(0,strlen($characters)-1)];
        }

        return $password;
    }

    /**
     * Get passwd
     *
     * @return string 
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * Add activitylog
     *
     * @param PivotX\CoreBundle\Entity\ActivityLog $activitylogs
     */
    public function addActivityLog(\PivotX\CoreBundle\Entity\ActivityLog $activitylogs)
    {
        $this->activitylogs[] = $activitylogs;
    }

    /**
     * Get activitylogs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivitylogs()
    {
        return $this->activitylogs;
    }


    /**
     * Implements UserInterface
     */

    function getRoles()
    {
        switch ($this->level) {
            case 200:
                return array('ROLE_EDITOR');
                break;

            case 800:
                return array('ROLE_DEVELOPER');
                break;

            case 900:
                return array('ROLE_SUPER_ADMIN');
                break;

            default:
                return array('ROLE_USER');
                break;
        }

        return array('ROLE_ANONYMOUS');
    }

    /**
     * Add a specific role to this user
     *
     * @todo incomplete interface
     */
    public function addRole($role)
    {
        switch ($role) {
            case 'ROLE_SUPER_ADMIN':
                if ($this->level < 900) {
                    $this->level = 900;
                }
                break;
        }
    }

    function getPassword()
    {
        return $this->getPasswd();
    }

    function getSalt()
    {
        return $this->getPasswdSalt();
    }

    function getUsername()
    {
        return $this->getEmail();
    }

    function eraseCredentials()
    {
        // @todo do something!
    }

    function equals(UserInterface $user)
    {
        if ($user->getUsername() == $this->getUsername()) {
            return true;
        }
        return false;
    }


    /**
     * Crud methods
     */

    public function initNewCrudRecord()
    {
        $this->date_created    = new \DateTime();
        $this->date_modified   = new \DateTime();
        $this->date_last_login = null;
    }




















































































































































































































































































































































































































































































































































































































































































































































































































































































    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_date_created()
    {
        return array(
            'name' => 'date_created',
            'type' => false
        );
    }

    /**
     * PrePersist the creation timestamp
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function setPrePersist_date_created()
    {
        if (is_null($this->date_created)) {
            $this->date_created = new \DateTime;
        }
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_date_modified()
    {
        return array(
            'name' => 'date_modified',
            'type' => false
        );
    }

    /**
     * PrePersist the update timestamp
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function setPrePersist_date_modified()
    {
        $this->date_modified = new \DateTime;
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_date_last_login()
    {
        return array(
            'name' => 'date_last_login',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_level()
    {
        return array(
            'name' => 'level',
            'type' => 'choice',
           'choices' => array(
               '100' => 'Site access (no PivotX access)',
               '200' => 'Editorial access',
               '500' => 'Administrative access',
               '800' => 'Developer access',
               '900' => 'Superadmin access'
           )
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_passwd_salt()
    {
        return array(
            'name' => 'passwd_salt',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_passwd()
    {
        return array(
            'name' => 'passwd',
            'type' => 'repeated',
            'arguments' => array(
                'type' => 'password',
                'first_name' => 'passwd',
                'second_name' => 'passwd_repeat'
            ),
            'setencoderfactory' => 'setEncoderFactory_passwd'
        );
    }

    /**
     * Set the encoder factory
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function setEncoderFactory_passwd($encoder_factory)
    {
        $this->encoder_factory_passwd = $encoder_factory;
    }

}
