<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultFrontController extends Controller
{
    /**
     * Return default HTML context parameters
     */
    public function getDefaultHtmlContext()
    {
        $context = parent::getDefaultHtmlContext();

        $context['debug'] = $this->get('kernel')->isDebug();
        $context['developer_help'] = $this->get('kernel')->isDebug();

        return $context;
    }

    /**
     * Return the entity class
     *
     * @todo as noted in BackendBundle/PivotX/Backend/Views/findEntities we now assume an array which should be an object
     * @todo do some role checking here?
     *
     * @param string $name  name of the entity
     * @return string       entity class name
     */
    private function getEntityClassByName($name)
    {
        $entity = $this->get('pivotx.siteoptions')->getValue('entities.entity.'.strtolower($name), array(), 'all');

        if ((count($entity) > 0) && (isset($entity['entity_class']))) {
            return $entity['entity_class'];
        }

        return null;
    }

    public function showErrorAction(Request $request)
    {
        $parameters = $this->getDefaultHtmlContext();

        $http_status = $request->attributes->get('_http_status', 500);

        $views = array();

        $views[] = 'CoreBundle:Errors:'.$http_status.'.html.twig';
        $views[] = 'CoreBundle:Errors:500.html.twig';
        $views[] = 'CoreBundle:Errors:unconfigured.html.twig';

        $response = $this->render($views, $parameters);
        $response->setStatusCode($http_status);

        return $response;
    }

    private function _showEntityTemplate(Request $request, $entity, $entity_class, $record)
    {
        $parameters = $this->getDefaultHtmlContext();

        $parameters['entity'] = $entity;
        $parameters[$entity]  = $record;

        // only our default template should use the following variable:
        // (because twig doesn't allow variable variable names (and shouldn't by default))
        $parameters['unusable_record'] = $record;

        $views = array();

        $views[] = 'CoreBundle:Default:entity.html.twig';

        return $this->render($views, $parameters);
    }

    public function showEntityBySlugAction(Request $request, $slug)
    {
        $parameters = $this->getDefaultHtmlContext();

        $views = array();

        $views[] = 'CoreBundle:Default:entity.html.twig';

        return $this->render($views, $parameters);
    }

    public function showEntityByIdAction(Request $request, $id)
    {
        if (!$request->attributes->has('_entity')) {
            return $this->forwardByReference('_http/404');
        }

        $entity       = $request->attributes->get('_entity');
        $entity_class = $this->getEntityClassByName($entity);
        if (is_null($entity_class)) {
            return $this->forwardByReference('_http/500');
        }

        $repository = $this->get('doctrine')->getRepository($entity_class);
        $record = $repository->find($id);

        if (is_null($record)) {
            return $this->forwardByReference('_http/404');
        }

        return $this->_showEntityTemplate($request, $entity, $entity_class, $record);
    }
}
