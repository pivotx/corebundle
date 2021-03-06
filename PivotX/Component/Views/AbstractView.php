<?php

namespace PivotX\Component\Views;

// @todo should be abstract
class AbstractView implements ViewInterface
{
    protected $name = null;
    protected $group = null;
    protected $tags = array();
    protected $description = null;
    protected $long_description = null;
    protected $code_examples = array();

    protected $arguments = array();
    protected $range_offset = null;
    protected $range_limit = null;

    protected $query_arguments = array();


    public function __construct($name, $group = 'Ungrouped', $description = null, $tags = null)
    {
        $this->name        = $name;
        $this->group       = $group;
        $this->description = $description;

        if (is_array($tags)) {
            $this->tags = $tags;
        }
    }


    /**
     * Get the name of the view
     *
     * @return string view name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the group of the view
     *
     * @return string group name
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get the tags of the view
     */
    public function getTags()
    {
        if (!is_array($this->tags)) {
            return array();
        }
        return $this->tags;
    }
    



    /**
     * Set-up the arguments for the view
     *
     * @param array $arguments arguments to use
     */
    public function setArguments(array $arguments = null)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Add 'with' arguments to the view
     *
     * @param array $arguments arguments to add
     */
    public function addWithArguments(array $arguments = null)
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    /**
     * Set output result range
     *
     * @param integer $limit   limits the number of results
     * @param integer $offset  offset to start with
     * @return $this
     */
    public function setRange($limit = null, $offset = null)
    {
        $this->range_limit  = $limit;
        $this->range_offset = $offset;

        return $this;
    }

    /**
     * Set the output result range using pages
     *
     * @param integer $page   current page number (base 1)
     * @param integer $size   page size
     * @return $this
     */
    public function setCurrentPage($page, $size)
    {
        $this->range_offset = ($page - 1) * $size;
        $this->range_limit  = $size;
    }

    /**
     * Get results of the view
     *
     * @return mixed           the view result
     */
    // @todo should be abstract
    public function getResult()
    {
        return array();
    }

    /**
     * Return the total number of results
     *
     * @return integer return the total number of results for the result-set
     */
    // @todo should be abstract
    public function getLength()
    {
        return 0;
    }

    /**
     * Set query arguments
     *
     * @param array $arguments the query arguments
     */
    public function setQueryArguments($arguments = array())
    {
        $this->query_arguments = $arguments;
    }

    /**
     * Return query arguments
     *
     * @return array the query arguments
     */
    // @todo should be abstract
    public function getQueryArguments($more_arguments = array())
    {
        if (!is_array($this->query_arguments)) {
            $this->query_arguments = array();
        }
        if (is_array($more_arguments) && (count($more_arguments) > 0)) {
            return array_merge($this->query_arguments, $more_arguments);
        }
        return $this->query_arguments;
    }

    /**
     * Get the current page number
     *
     * @return integer current page number (base 1), or 0 if cannot be determined
     */
    public function getCurrentPage()
    {
        if (is_null($this->range_limit) || is_null($this->range_offset)) {
            return 0;
        }
        if ($this->range_limit < 1) {
            return 0;
        }
        return floor($this->range_offset / $this->range_limit) + 1;
    }

    /**
     * Return the total number of pages of results
     *
     * @return integer return the total number of pages of results for the result-set
     */
    public function getNoOfPages()
    {
        if (is_null($this->range_limit)) {
            return 0;
        }

        return ceil($this->getLength() / $this->range_limit);
    }


    /**
     * Get a developer description of the view
     *
     * @return string view description (falls back to name)
     */
    public function getDescription()
    {
        if (!is_null($this->description)) {
            return $this->description;
        }
        return 'No description for "'.$this->name.'"';
    }

    /**
     * Set the long developer description
     *
     * @param string $long_description
     */
    public function setLongDescription($long_description)
    {
        $this->long_description = $long_description;
    }

    /**
     * Get the long developer description of the view
     *
     * @return string view long description
     */
    public function getLongDescription()
    {
        if (!is_null($this->long_description)) {
            return $this->long_description;
        }
        return false;
    }

    /**
     */
    public function getDefaultTwigExample()
    {
        $name       = $this->getName();
        $resultname = 'items';
        $loopvar    = 'item';

        if (count($this->tags) > 0) {
            $singular = strtolower($this->tags[0]);

            $resultname = \PivotX\Component\Translations\Inflector::pluralize($singular);
            $loopvar    = $singular;
        }

        $code = <<<THEEND
{% loadView '$name' as $resultname with <span class="arguments">arguments</span> %}
&lt;ul&gt;
{% for $loopvar in $resultname %}
    &lt;li&gt;
        {{ $loopvar.title }}
    &lt;/li&gt;
{% endfor %}
&lt;/ul&gt;
THEEND;

        return $code;
    }

    public function getCodeExamples()
    {
        if (count($this->code_examples) == 0) {
            return array(
                'Twig example' => array('twig', $this->getDefaultTwigExample())
            );
        }
        return $this->code_examples;
    }

    /**
     */
    public function getHelpPages()
    {
        $pages = array();

        if ($this->getLongDescription() != '') {
            $text = $this->getLongDescription();
            $type = 'default';
            if (!($text instanceof \Twig_Markup)) {
                $text = new \Twig_Markup($text, 'utf-8');
            }

            $pages[] = array(
                'title' => 'Documentation',
                'type' => $type,
                'text' => $text,
            );
        }

        foreach($this->getCodeExamples() as $title => $example) {
            list($type, $text) = $example;

            $pages[] = array(
                'title' => $title,
                'type' => $type,
                'text' => $text
            );
        }

        return $pages;
    }

    /**
     * Return a single value from the result
     */
    public function getValue()
    {
        $result = $this->getResult();
        if (is_array($result) && (count($result) > 0)) {
            return $result[0];
        }
        if ($result instanceof \Iterator) {
            list($key,$value) = each($result);
            $result->rewind();
            return $value;
        }
        return null;
    }

    /**
     * Return an iterator
     */
    public function getIterator()
    {
        return new \PivotX\Component\Twig\HtmlIterator($this->getResult());
    }

    /**
     * Return a HTML iterator
     */
    public function getHtmlIterator($args = null)
    {
        return new \PivotX\Component\Twig\HtmlIterator($this->getResult(), $args);
    }

    /**
     */
    public function __toString()
    {
        return 'View/'.$this->getName();
    }
}
