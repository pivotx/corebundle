<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
class SiteOption 
{
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
     * Crud defaults
     */


































































































































































































































































































































    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-05, 14:02:11
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
     * Generated on 2012-11-05, 14:02:11
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
     * Generated on 2012-11-05, 14:02:11
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
     * Generated on 2012-11-05, 14:02:11
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
     * Generated on 2012-11-05, 14:02:11
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
            )
        );
    }

}
