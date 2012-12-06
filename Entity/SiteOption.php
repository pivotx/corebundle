<?php

namespace PivotX\CoreBundle\Entity;

use Symfony\Component\Yaml\Yaml;

/**
 */
class SiteOption 
{
    /**
     * Required for PivotX/Doctrine loggable
     */
    private static $activity_service = null;

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
     * @var boolean $autoload
     */
    private $autoload;

    /**
     * @var boolean $human_editable
     */
    private $human_editable;

    /**
     * @var string $mediatype
     */
    private $mediatype;

    /**
     * @var text $value
     */
    private $value;

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
     * Set autoload
     *
     * @param boolean $autoload
     */
    public function setAutoload($autoload)
    {
        $this->autoload = $autoload;
    }

    /**
     * Get autoload
     *
     * @return string 
     */
    public function getAutoload()
    {
        return $this->autoload;
    }

    /**
     * Set human_editable
     *
     * @param boolean $human_editable
     */
    public function setHumanEditable($human_editable)
    {
        $this->human_editable = $human_editable;
    }

    /**
     * Get human_editable
     *
     * @return string 
     */
    public function getHumanEditable()
    {
        return $this->human_editable;
    }

    /**
     * Set mediatype
     *
     * @param string $mediatype
     */
    public function setMediatype($mediatype)
    {
        $this->mediatype = $mediatype;
    }

    /**
     * Get mediatype
     *
     * @return string 
     */
    public function getMediatype()
    {
        return $this->mediatype;
    }

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get unpacked value
     *
     * @return mixed
     */
    public function getUnpackedValue()
    {
        switch ($this->mediatype) {
            case 'application/json':
                return json_decode($this->value, true);
                break;

            case 'text/x-yaml':
                return Yaml::parse($this->value);
                breal;

            case 'x-value/boolean':
                return $this->value == '1' ? true : false;
                breal;
        }

        return $this->value;
    }

    /**
     * Set unpacked value
     *
     * Pack the value and set it
     *
     * @param mixed $value
     */
    public function setUnpackedValue($value)
    {
        switch ($this->mediatype) {
            case 'application/json':
                $this->value = json_encode($value);
                return true;
                break;

            case 'text/x-yaml':
                $this->value = Yaml::dump($value);
                return true;
                break;
        }

        return false;
    }


    /**
     * Crud defaults
     */

    /**
     * Store a version
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-20, 11:07:28
     */
    public function preUpdate_Loggable()
    {
        $fields = array( "sitename","groupname","name","date_created","date_modified","autoload","human_editable","mediatype","value" );

        $data   = array();
        foreach($fields as $field) {
            $data[$field] = $this->$field;
        }

        if ((property_exists('PivotX\CoreBundle\Entity\SiteOption', 'activity_service')) && (!is_null(self::$activity_service))) {
            $log = self::$activity_service->createLoggableMessage(
                'en',
                'Stored a version of %classname% with id %id%',
                array( '%classname%' => "SiteOption", '%id%' => $this->getId() ),
                $data
            );
        }
    }

    /**
     * Store a version
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-20, 11:13:58
     */
    public function onFlush_Loggable()
    {
        $fields = array( "sitename","groupname","name","date_created","date_modified","autoload","human_editable","mediatype","value" );

        $data   = array();
        foreach($fields as $field) {
            $data[$field] = $this->$field;
        }

        if ((property_exists('PivotX\CoreBundle\Entity\SiteOption', 'activity_service')) && (!is_null(self::$activity_service))) {
            $log = self::$activity_service->createLoggableMessage(
                'en',
                'Stored a version of %classname% with id %id%',
                array( '%classname%' => "SiteOption", '%id%' => $this->getId() ),
                $data
            );
        }
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
    public function getCrudConfiguration_mediatype()
    {
        return array(
            'name' => 'mediatype',
            'type' => 'choice',
            'choices' => array(
                'text/x-line' => 'Single line',
                'text/plain' => 'Multiple lines',
                'text/html' => 'HTML',
                'text/xml' => 'XML',
                'text/x-yaml' => 'YAML',
                'application/json' => 'JSON',

                'x-value/boolean' => 'Boolean value',
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
    public function getCrudConfiguration_value()
    {
        $config = array(
            'name' => 'value',
            'type' => 'textarea'
        );

        switch ($this->mediatype) {
            case 'x-value/boolean':
                $config['type'] = 'choice';
                $config['choices'] = array(
                    '0' => 'no',
                    '1' => 'yes'
                );
                break;

            case 'text/x-line':
                break;

            case 'text/plain':
            case 'text/html':
            case 'text/xml':
            case 'text/x-yaml':
            case 'application/json':
                $config['type'] = 'textarea';
                break;
        }

        return $config;
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
        if (property_exists('PivotX\CoreBundle\Entity\SiteOption', 'activity_service')) {
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
        $fields = array( "sitename","groupname","name","date_created","date_modified","autoload","human_editable","mediatype","value" );

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

        if ($changes && (property_exists('PivotX\CoreBundle\Entity\SiteOption', 'activity_service')) && (!is_null(self::$activity_service))) {
            $log = self::$activity_service->createLoggableMessage('SiteOption', $this->getId(), $data);

            return $log;
        }

        return null;
    }

}
