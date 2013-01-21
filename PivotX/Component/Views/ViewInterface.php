<?php

namespace PivotX\Component\Views;


/**
 */
interface ViewInterface
{
    /**
     * Get the name of the view
     *
     * @return string view name
     */
    public function getName();

    /**
     * Get the group of the view
     *
     * @return string group name
     */
    public function getGroup();

    /**
     * Get the tags of the view
     */
    public function getTags();


    /**
     * Set-up the arguments for the view
     *
     * @param array $arguments arguments to use
     */
    public function setArguments(array $arguments = null);

    /**
     * Add 'with' arguments to the view
     *
     * @param array $arguments arguments to add
     */
    public function addWithArguments(array $arguments = null);

    /**
     * Set output result range
     *
     * @param integer $limit   limits the number of results
     * @param integer $offset  offset to start with
     * @param array $arguments arguments to use
     */
    public function setRange($limit = null, $offset = null);

    /**
     * Set the output result range using pages
     *
     * @param integer $page   current page number (base 1)
     * @param integer $size   page size
     * @return $this
     */
    public function setCurrentPage($page, $size);

    /**
     * Get result of the view
     *
     * @return mixed           the view result
     */
    public function getResult();

    /**
     * Return the total number of results
     *
     * @return integer return the total number of results for the result-set
     */
    public function getLength();

    /**
     * Set query arguments
     *
      @param array $arguments the query arguments
     */
    public function setQueryArguments($arguments = array());

    /**
     * Return query arguments
     *
      @return array the query arguments
     */
    public function getQueryArguments($more_arguments = array());

    /**
     * Get the current page number
     *
     * @return integer current page number (base 1), or 0 if cannot be determined
     */
    public function getCurrentPage();

    /**
     * Return the total number of pages of results
     *
     * @return integer return the total number of pages of results for the result-set
     */
    public function getNoOfPages();

    /**
     * Get a developer description of the view
     *
     * @return string view description
     */
    public function getDescription();

    /**
     * Return the value of the result
     * 
     * Even if the result is an array (returns the first)
     *
     * @return mixed return the value
     */
    public function getValue();

    /**
     * Return an iterator of the result
     * 
     * Even if the result is a single value
     *
     * @return Iterator return an iterator
     */
    public function getIterator();

    /**
     * Return a HTML iterator of the result
     * 
     * Even if the result is a single value
     *
     * @return Iterator return a HTML iterator
     */
    public function getHtmlIterator();
}
