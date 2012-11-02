<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
class RemoteEmbedResource extends EmbedResource
{
    protected $uri;
    protected $rawhtml;

    /**
     * Set uri
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set html
     *
     * @param text $rawhtml
     */
    public function setRawhtml($rawhtml)
    {
        $this->rawhtml = $rawhtml;
    }

    /**
     * Get html
     *
     * @return text 
     */
    public function getRawhtml()
    {
        return $this->rawhtml;
    }

    /**
     * Get the html to embed this
     */
    public function getHtml($width = null, $height = null, $scaleMethod = 'keep-aspect')
    {
        $html = $this->rawhtml;

        // @todo resize the html

        return $html;
    }






























































}
