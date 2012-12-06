<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
//class TranslationText extends \PivotX\Doctrine\Entity\AutoEntity
class TranslationText 
{
    /**
     * Required for PivotX/Doctrine loggable
     */
    private static $activity_service = null;

    /**
     *  0 - key/text is valid
     * 10 - key/text has a suggested value
     * 20 - key/text is auto-filled (usually with a key name variant)
     * 21 - key/text is auto-filled with lorem ipsum
     * 29 - key/text was no longer used, but is used again (see 90)
     * 90 - key/text is no longer used, allow it to be re-added (see 29)
     * 91 - key/text is no longer used, don't allow it to be re-added
     */
    const STATE_VALID = 0;
    const STATE_SUGGESTED = 10;
    const STATE_AUTO_TECHNICAL = 20;
    const STATE_AUTO_LOREM = 21;
    const STATE_AUTO_REUSED = 29;
    const STATE_OLD = 90;
    const STATE_OLD_LOCKED = 91;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $sitename
     */
    private $sitename;

    /**
     * @var string $groupname
     */
    private $groupname;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var datetime $date_created
     */
    private $date_created;

    /**
     * @var datetime $date_modified
     */
    private $date_modified;

    /**
     * @var integer $state
     */
    private $state;

    /**
     * @var string $encoding
     */
    private $encoding;

    /**
     * @var string $text_nl
     */
    private $text_nl;

    /**
     * @var string $text_en
     */
    private $text_en;

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
     * Set sitename
     *
     * @param string $sitename
     */
    public function setSitename($sitename)
    {
        $this->sitename = $sitename;
    }

    /**
     * Get sitename
     *
     * @return string 
     */
    public function getSitename()
    {
        return $this->sitename;
    }

    /**
     * Set groupname
     *
     * @param string $groupname
     */
    public function setGroupname($groupname)
    {
        $this->groupname = $groupname;
    }

    /**
     * Get groupname
     *
     * @return string 
     */
    public function getGroupname()
    {
        return $this->groupname;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set state
     *
     * @param integer $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set encoding
     *
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Get encoding
     *
     * @return string 
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set text_nl
     *
     * @param text $textnl
     */
    public function setTextNl($textnl)
    {
        $this->text_nl = $textnl;
    }

    /**
     * Get text_nl
     *
     * @return text 
     */
    public function getTextNl()
    {
        return $this->text_nl;
    }

    /**
     * Set text_en
     *
     * @param text $texten
     */
    public function setTextEn($texten)
    {
        $this->text_en = $texten;
    }

    /**
     * Get text_en
     *
     * @return text 
     */
    public function getTextEn()
    {
        return $this->text_en;
    }


    /**
     * Crud specifics
     */

    /**
     * Crud text focus specific
     */
    public function getCrudFormArguments_text_nl()
    {
        return array(
            'attr' => array(
                'widget_class' => 'primary-focus',
            )
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
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
     * Generated on 2012-12-06, 17:47:18
     */
    public function prePersist_date_created()
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
     * Generated on 2012-12-06, 17:47:18
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
     * Generated on 2012-12-06, 17:47:18
     */
    public function prePersist_date_modified()
    {
        $this->date_modified = new \DateTime;
    }

    /**
     * PrePersist the update timestamp
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function preUpdate_date_modified()
    {
        $this->date_modified = new \DateTime;
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function getCrudConfiguration_state()
    {
        return array(
            'name' => 'state',
            'type' => 'choice',
            'choices' => array(
                self::STATE_VALID => 'valid',
                self::STATE_SUGGESTED => 'suggested value',
                self::STATE_AUTO_TECHNICAL => 'auto-filled with key name logic',
                self::STATE_AUTO_LOREM => 'auto-filled with lorem ipsum',
                self::STATE_AUTO_REUSED => 'old value is reused',
                self::STATE_OLD => 'old value, allow reuse',
                self::STATE_OLD_LOCKED => 'old value, don\'t allow reuse'
            )
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function getCrudConfiguration_encoding()
    {
        return array(
            'name' => 'encoding',
            'type' => 'choice',
            'choices' => array(
                'utf-8' => 'text/UTF-8',
                'utf-8/html' => 'html/UTF-8'
            )
        );
    }

    /**
     * Set the activityservice
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public static function setActivityService($service)
    {
        if (property_exists('PivotX\CoreBundle\Entity\TranslationText', 'activity_service')) {
            self::$activity_service = $service;
        }
    }

    /**
     * Store a version
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function onPxPreUpdate_Loggable($changeset)
    {
        $fields = array( "sitename","groupname","name","date_created","date_modified","state","encoding","text_nl","text_en" );

        $data    = array();
        $changes = false;
        foreach($fields as $field) {
            $data[$field] = $this->$field;

            if (isset($changeset[$field])) {
                $data[$field] = $changeset[$field][0];
                $changes      = true;
            }
        }

        // @todo not the nicest way, but works for now
        if (isset($this->loggable_already_logged)) {
            $changes = false;
        }
        $this->loggable_already_logged = true;

        if ($changes && (property_exists('PivotX\CoreBundle\Entity\TranslationText', 'activity_service')) && (!is_null(self::$activity_service))) {
            $log = self::$activity_service->createLoggableMessage('TranslationText', $this->getId(), $data);

            return $log;
        }

        return null;
    }

}
