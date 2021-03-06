<?php

namespace PivotX\CoreBundle\Entity;
use PivotX\Doctrine\Annotation as PivotX;

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
    protected $meta;

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
     * Set meta information
     *
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = json_encode($meta);
    }

    /**
     * Get meta information
     *
     * @return mixed 
     */
    public function getMeta()
    {
        return json_decode($this->meta, true);
    }

    /**
     * Return resource information
     */
    public function getFileInfo()
    {
        return array(
            'valid' => true,
            'id' => $this->getId(),
            'mimetype' => $this->media_type,
            'title' => $this->title
        );
    }

    /**
     * By default not embeddable
     */
    public function isEmbeddable()
    {
        return false;
    }

    /**
     * By default not downloadable
     */
    public function isDownloadable()
    {
        return false;
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
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-07 16:40:15
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function getCrudConfiguration_media_type()
    {
        return array(
            'name' => 'media_type',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
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
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function prePersist_date_created()
    {
        $this->date_created = new \DateTime;
    }

    /**
     * PrePersist the update timestamp
     * 
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function preUpdate_date_created()
    {
        $this->date_created = new \DateTime;
    }

    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
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
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function prePersist_date_modified()
    {
        $this->date_modified = new \DateTime;
    }

    /**
     * PrePersist the update timestamp
     * 
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function preUpdate_date_modified()
    {
        $this->date_modified = new \DateTime;
    }

    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function getCrudConfiguration_meta()
    {
        return array(
            'name' => 'meta',
            'type' => false
        );
    }

    /**
     * Returns the generic title for this object
     *
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function getGenericTitle()
    {
        return $this->title;
    }


    /**
     * Returns the generic description for this object
     *
     * @PivotX\UpdateDate     2013-01-11 09:16:28
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function getGenericDescription()
    {
        return $this->author;
    }


}
