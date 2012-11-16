<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

use PivotX\Component\Routing\Service as RoutingService;
use PivotX\Component\Translations\Service as TranslationsService;
use PivotX\Component\Formats\Service as FormatsService;
use PivotX\Component\Webresourcer\Service as WebresourcerService;
use PivotX\Component\Outputter\Service as OutputterService;

/**
 * Twig Query interface
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service extends \Twig_Extension
{
    protected $environment = false;
    protected $pivotx_routing = false;
    protected $pivotx_translations = false;
    protected $pivotx_formats = false;
    protected $pivotx_webresourcer = false;
    protected $pivotx_outputter = false;

    /**
     */
    public function __construct(RoutingService $pivotx_routing, TranslationsService $pivotx_translations, FormatsService $pivotx_formats, WebresourcerService $pivotx_webresourcer, OutputterService $pivotx_outputter)
    {
        $this->pivotx_routing      = $pivotx_routing;
        $this->pivotx_translations = $pivotx_translations;
        $this->pivotx_formats      = $pivotx_formats;
        $this->pivotx_webresourcer = $pivotx_webresourcer;
        $this->pivotx_outputter    = $pivotx_outputter;
    }

    /**
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName()
    {
        return 'pivotx';
    }

    public function getFunctions()
    {
        return array(
            'ref' =>  new \Twig_Function_Method($this, 'getReference'),
            'translate' => new \Twig_Function_Method($this, 'getTranslate'),
            'pagination' => new \Twig_Function_Method($this, 'getPagination'),
            'outputter' => new \Twig_Function_Method($this, 'getOutputter')
        );
    }

    public function getFilters()
    {
        return array(
            'formatas' => new \Twig_Filter_Method($this, 'filterFormatAs'),
            'htmliterator' => new \Twig_Filter_Method($this, 'filterHtmlIterator')
        );
    }

    public function getTokenParsers()
    {
        return array(
            new Loadview(),
            new Loadlist()
        );
    }

    /**
     * Build an URL based on a Reference
     *
     * Shortcut into the Reference/Routing system
     * 
     * @param mixed $text      Either a TextReference (=string) or an array to implode
     * @param array $arguments Query arguments to add to URL
     * @param array $options   URL options see, Routing/Service::buildUrl
     * @return string          URL
     */
    public function getReference($text, $arguments = array(), $options = array())
    {
        if (is_array($text)) {
            $text = implode('', $text);
        }

        $url = $this->pivotx_routing->buildUrl($text, $arguments, $options);

        return $url;
    }

    /**
     * Shortcut into the translation system
     */
    public function getTranslate($key, $sitename = null, $encoding = 'utf-8')
    {
        if (is_array($key)) {
            $key = implode('', $key);
        }
        return $this->pivotx_translations->translate(mb_strtolower($key), $sitename, $encoding);
    }

    /**
     * Fast pagination filler
     *
     * @todo support for multiple kinds
     * @todo get this code out of here
     */
    public function getPagination(\PivotX\Component\Views\ViewInterface $view, $link, $link_arguments = array(), $pagination_variable = 'page')
    {
        $show_pages     = 9;
        $want_firstlast = true;
        $want_prevnext  = true;
        $no_of_pages    = $view->getNoOfPages();

        $current_page = $view->getCurrentPage();

        $first_page = $current_page - floor($show_pages / 2);
        if ($first_page < 1) {
            $first_page = 1;
        }

        $last_page = $first_page + $show_pages - 1;
        if ($last_page > $no_of_pages) {
            $last_page = $no_of_pages;
        }


        // with accept a link as array
        if (is_array($link)) {
            $link = implode('', $link);
        }


        // update the actual link with all arguments

        $paginationmacro = 'PAGINATION';
        $link_arguments[$pagination_variable] = $paginationmacro;

        $query_arguments = $view->getQueryArguments($link_arguments);


        $array = array();

        $base_link = $this->getReference($link, $query_arguments);

        if (($current_page > 1) && ($want_firstlast)) {
            $page_link = str_replace($paginationmacro, '1', $base_link);
            $array[] = array( 'page' => 1, 'class' => 'first', 'title' => $this->getTranslate('pagination.first-page'), 'link' => $page_link );
        }
        if (($current_page > 1) && ($want_prevnext)) {
            $page_link = str_replace($paginationmacro, ($current_page-1), $base_link);
            $array[] = array( 'page' => $current_page-1, 'class' => 'previous', 'title' => $this->getTranslate('pagination.previous-page'), 'link' => $page_link );
        }

        for($page=$first_page; $page <= $last_page; $page++) {
            $page_link = str_replace($paginationmacro, $page, $base_link);
            $class     = false;

            if ($page == $current_page) {
                $class = 'active';
            }

            $array[] = array ( 'page' => $page, 'class' => $class, 'title' => $page, 'link' => $page_link );
        }

        if (($current_page < $no_of_pages) && ($last_page != $no_of_pages)) {
            $page_link = str_replace($paginationmacro, $no_of_pages, $base_link);

            $array[] = array ( 'page' => false, 'class' => 'disabled', 'title' => '...', 'link' => false );

            $array[] = array ( 'page' => $no_of_pages, 'class' => false, 'title' => $no_of_pages, 'link' => $page_link );
        }

        if (($current_page < $no_of_pages) && ($want_prevnext)) {
            $page_link = str_replace($paginationmacro, ($current_page+1), $base_link);
            $array[] = array( 'page' => $current_page+1, 'class' => 'next', 'title' => $this->getTranslate('pagination.next-page'), 'link' => $page_link );
        }
        if (($current_page < $no_of_pages) && ($want_firstlast)) {
            $page_link = str_replace($paginationmacro, $no_of_pages, $base_link);
            $array[] = array( 'page' => $no_of_pages, 'class' => 'last', 'title' => $this->getTranslate('pagination.last-page'), 'link' => $page_link );
        }

        $view = new \PivotX\Component\Views\ArrayView($array, 'Common/Pagination', 'PivotX/Core', 'This is a dynamic view for pagination');

        return $view;
    }

    /**
     * Shortcut into the translation system
     */
    public function getOutputter($group)
    {
        return $this->pivotx_outputter->getOutputs($group);
    }

    public function filterFormatAs($in, $name = '')
    {
        $out = $in;

        $format = $this->pivotx_formats->findFormat($name);
        if (!is_null($format)) {
            $arguments = array();
            if (func_num_args() > 2) {
                $arguments = func_get_args();
                array_shift($arguments);
                array_shift($arguments);
            }
            $out = $format->format($in, $arguments);
        }

        return $out;
    }

    public function filterHtmlIterator($in, $active_key = null, $active_value = null)
    {
        $args = array();
        if (!is_null($active_key)) {
            $args['active_key'] = $active_key;
        }
        if (!is_null($active_value)) {
            $args['active_value'] = $active_value;
        }
        return new HtmlIterator($in, $args);
    }
}
