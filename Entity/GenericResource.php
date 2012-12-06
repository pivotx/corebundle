<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
class GenericResource
{
    protected $id;
    protected $publicid;
    protected $type;
    protected $date_created;
    protected $date_modified;
    protected $title;
    protected $author;
    protected $media_type;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getResourceType()
    {
        $name = get_class($this);
        if (($pos = strrpos($name, '\\')) > 0) {
            return substr($name, $pos+1);
        }
        return $name;
    }

    /**
     * Set publicid
     *
     * @param string $publicid
     */
    public function setPublicid($publicid)
    {
        $this->publicid = $publicid;
    }

    /**
     * Get publicid
     *
     * @return publicid 
     */
    public function getPublicid()
    {
        return $this->publicid;
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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set media_type
     *
     * @param string $mediaType
     */
    public function setMediaType($mediaType)
    {
        $this->media_type = $mediaType;
    }

    /**
     * Get media_type
     *
     * @return string 
     */
    public function getMediaType()
    {
        return $this->media_type;
    }


    /**
     * Crud defaults
     */

    /**
     */
    public function initNewCrudRecord()
    {
        $this->date_created  = new \DateTime();
        $this->date_modified = new \DateTime();
    }

    /**
     */
    public function getCrudRootEntity()
    {
        return 'GenericResource';
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
     * PrePersist the update timestamp
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function prePersist_date_created()
    {
        $this->date_created = new \DateTime;
    }

    /**
     * PrePersist the update timestamp
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function preUpdate_date_created()
    {
        $this->date_created = new \DateTime;
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
    public function getCrudConfiguration_media_type()
    {
        return array(
            'name' => 'media_type',
            'type' => false
        );
    }

}
