<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultFrontController extends Controller
{
    public function showEntityBySlugAction(Request $request, $slug)
    {
        $parameters = $this->getDefaultHtmlContext();

        return $this->render(null, $parameters);
    }

    public function showEntityByIdAction(Request $request, $id)
    {
        $parameters = $this->getDefaultHtmlContext();

        return $this->render(null, $parameters);
    }
}
