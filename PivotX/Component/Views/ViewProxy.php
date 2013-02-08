<?php
namespace PivotX\Component\Views;


class ViewProxy implements ViewInterface
{
    /**
     */
    public function __construct($name, $view_callback)
    {
        $this->name          = $name;
        $this->view_callback = $view_callback;
    }

    public function getName()
	{
        return $this->name;
	}

    public function createRealView()
    {
        return call_user_func($this->view_callback);
    }



    /**
     * Everything below is here is just empty code
     */

    public function getGroup()
	{
	}


    public function getTags()
	{
	}


    public function setArguments(array $arguments = null)
	{
	}


    public function addWithArguments(array $arguments = null)
	{
	}


    public function setRange($limit = null, $offset = null)
	{
	}


    public function setCurrentPage($page, $size)
	{
	}


    public function getResult()
	{
	}


    public function getLength()
	{
	}


    public function setQueryArguments($arguments = array())
	{
	}


    public function getQueryArguments($more_arguments = array())
	{
	}


    public function getCurrentPage()
	{
	}


    public function getNoOfPages()
	{
	}


    public function getDescription()
	{
	}


    public function getValue()
	{
	}


    public function getIterator()
	{
	}


    public function getHtmlIterator()
	{
	}

}
