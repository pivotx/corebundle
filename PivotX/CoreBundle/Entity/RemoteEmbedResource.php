<?php

namespace PivotX\CoreBundle\Entity;
use PivotX\Doctrine\Annotation as PivotX;

/**
 */
class RemoteEmbedResource extends EmbedResource
{
    protected $uri;
    protected $responses;

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
     * @param text $responses
     */
    public function setResponses($responses)
    {
        if (is_array($responses)) {
            $this->responses = json_encode($responses);
        }
        else {
            $this->responses = $responses;
        }
    }

    /**
     * Get html
     *
     * @return text 
     */
    public function getResponses()
    {
        if (!is_null($this->responses)) {
            if (is_array($this->responses)) {
                return $this->responses;
            }
            return json_decode($this->responses, true);
        }
        return null;
    }

    /**
     * Find the OEmbed URI from our own list
     *
     * @param string $uri    URI entered by user
     * @return mixed         OEmbed URI or null if not found
     */
    private function findOEmbedUri($uri)
    {
        return null;
    }

    /**
     * Discover the OEmbed URI
     *
     * @param string $uri    URI entered by user
     * @return mixed         OEmbed URI or null if not found
     */
    private function discoverOEmbedUri($uri)
    {
        echo getcwd();
        die();
        $content = file_get_contents($uri);

        if ($content !== false) {
            /*
            <link rel="alternate" type="application/json+oembed"
                  href="http://flickr.com/services/oembed?url=http%3A%2F%2Fflickr.com%2Fphotos%2Fbees%2F2362225867%2F&format=json"
                    title="Bacon Lollys oEmbed Profile" />
            */
            if (preg_match_all('|<link [^>]+?>|', $content, $matches)) {
                //var_dump($matches);
            }
        }

        return null;
    }

    /**
     */
    public function executeOEmbed($uri, $width, $height)
    {
        $oembed_uri = $this->findOEmbedUri($uri);
        if (is_null($oembed_uri)) {
            $oembed_uri = $this->discoverOEmbedUri($uri);
        }

        if (!is_null($oembed_uri)) {
            $content = file_get_contents($oembed_uri);
        }

        return null;
    }

    /**
     * Get the html to embed this
     */
    public function getHtml($inWidth = null, $inHeight = null, $scaleMethod = 'keep-aspect', $options = null)
    {
        $responses = $this->getResponses();

        list($width, $height) = $this->determineWidthAndHeight($inWidth, $inHeight, $scaleMethod, $options);
        $key = sprintf('%sx%s', is_null($width)?'null':$width,is_null($height)?'null':$height);

        if (!isset($responses[$key])) {
            $response = $this->executeOEmbed($this->uri, $width, $height);

            if (!is_null($response)) {
                $responses[$key] = $response;

                $this->setReponses($responses);
            }
        }

        // @todo resize the html

        return $html;
    }


}
