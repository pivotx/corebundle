<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as FwController;


class DefaultController extends FwController
{
    public function indexAction($name)
    {
        return $this->render('PivotXCoreBundle:Default:index.html.twig', array('name' => $name));
    }
}
