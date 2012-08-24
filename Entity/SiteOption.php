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
     * @var text $value
     */
    private $value;
}
