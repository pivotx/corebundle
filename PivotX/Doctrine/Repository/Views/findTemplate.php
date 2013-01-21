<?php

namespace PivotX\Doctrine\Repository\Views;

use \PivotX\Component\Views\AbstractView;

class findTemplate extends AbstractView
{
    protected $repository;
    protected $method;
    protected $method_arguments;

    public function __construct($repository, $method, $arguments, $name, $description, $group, $tags)
    {
        parent::__construct($name, $group, $description, $tags);

        $this->repository       = $repository;
        $this->method           = $method;
        $this->method_arguments = $arguments;
    }

    /**
     */
    public function getDefaultPhpExample()
    {
        $name         = $this->getName();
        $repos_class = get_class($this->repository);
        $resultname   = 'items';
        $loopvar      = 'item';
        $method       = $this->method;

        $entity_class = str_replace('\\', '\\\\', str_replace('Repository', '', str_replace('Model', 'Entity', $repos_class)));

        if (count($this->tags) > 0) {
            $singular = strtolower($this->tags[0]);

            $resultname = \PivotX\Component\Translations\Inflector::pluralize($singular);
            $loopvar    = $singular;
        }

        $method_title = '->getTitle()';

        $code = <<<THEEND
<code>\$repository  = \$doctrine->getRepository('$entity_class');
\$$resultname = \$repository->$method(<span class="argument">...</span>);

\$out = '';
foreach(\$$resultname as \$$loopvar) {
    \$out .= \$$loopvar$method_title;
}</code>
THEEND;

        return $code;
    }

    public function getCodeExamples()
    {
        if (count($this->code_examples) == 0) {
            return array(
                'Twig example' => array('twig', $this->getDefaultTwigExample()),
                'Php example' => array('php', $this->getDefaultPhpExample())
            );
        }
        return $this->code_examples;
    }

    private function splitArguments()
    {
        $criteria = array();
        $order_by = null;
        foreach($this->arguments as $k => $v) {
            if (in_array($k, array('orderBy'))) {
                $order_by = $v;
            }
            else {
                $criteria[$k] = $v;
            }
        }
        return array($criteria, $order_by);
    }

    private function executeQuery($limit = null, $offset = null)
    {
        list($criteria, $order_by) = $this->splitArguments();

        $array = array();

        foreach($this->method_arguments as $k => $v) {
            if (isset($this->arguments[$k])) {
                $v = $this->arguments[$k];
            }

            $array[] = $v;
        }

        $array[] = $criteria;
        $array[] = $order_by;
        $array[] = $limit;
        $array[] = $offset;

        return call_user_func_array(array($this->repository, $this->method), $array);
    }

    public function getResult()
    {
        return $this->executeQuery($this->range_limit, $this->range_offset);
    }

    public function getLength()
    {
        $data = $this->executeQuery();

        return count($data);
    }
}


