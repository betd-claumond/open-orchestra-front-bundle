<?php
/**
 * This file is part of the PHPOrchestra\CMSBundle.
 *
 * @author Noël Gilain <noel.gilain@businessdecision.com>
 */

namespace PHPOrchestra\CMSBundle\Controller\BackOfficeView;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PHPOrchestra\CMSBundle\Exception\UnrecognizedCommandTypeException;
use Symfony\Component\HttpFoundation\JsonResponse;

class BackOfficeController extends Controller
{
    /**
     * Back Office Home Page
     */
    public function homeAction()
    {
        return $this->render('PHPOrchestraCMSBundle:BackOffice:home.html.twig');
    }
    
    /**
     * Editorial Rubric Home Page
     */
    public function editoAction()
    {
        return $this->render('PHPOrchestraCMSBundle:BackOffice/Editorial:editoHome.html.twig');
    }
    
    /**
     * Tree menu Dispatcher
     * 
     * @param string $cmd
     * @param Request $request
     */
    public function jsContextMenuDispatchAction($cmd, Request $request)
    {

        switch ($cmd)
        {
            case 'createNode':
            	$request->setMethod('GET');
            	return $this->forward('PHPOrchestraCMSBundle:Node:form', array());
            	break;
	        case 'confirmDeleteNode':
	            $response = $this->render(
	                'PHPOrchestraCMSBundle:BackOffice/Dialogs:confirmation.html.twig',
	                array(
	                    'dialogId' => '',
	                    'dialogTitle' => 'Suppression du node',
	                    'dialogMessage' => 'Vous êtes sur le point de supprimer le node "'.$request->request->get('name').'"<br /><br />Souhaitez-vous continuer ?',
	                )
	            );
	            return new JsonResponse(
	                array(
	                    'dialog' => $response->getContent(),
	                    'url' => $this->generateUrl('php_orchestra_cms_bo_jscontextmenudispatcher', array('cmd' => 'deleteNode')),
	                    'value' => array('nodeId' => $request->request->get('nodeId')),
	                )
	            );
	            break;
            case 'deleteNode':
            	$request->request->add(array('refreshRecord' => true));
            	$request->request->add(array('deleted' => true));
                return $this->forward('PHPOrchestraCMSBundle:Node:form', array('nodeId' => $request->request->get('nodeId')));
                break;
            case 'moveNode':
                $request->request->add(array('refreshRecord' => true));
                $node = $request->request->get('node');
                $parentNode = $request->request->get('parentNode');
                $request->request->add(array('parentId' => $parentNode['nodeId']));
                return $this->forward('PHPOrchestraCMSBundle:Node:form', array('nodeId' => $node['nodeId']));
                break;
            case 'createTemplate':
                $request->setMethod('GET');
                return $this->forward('PHPOrchestraCMSBundle:Template:form', array());
                break;
            case 'confirmDeleteTemplate':
                $response = $this->render(
                    'PHPOrchestraCMSBundle:BackOffice/Dialogs:confirmation.html.twig',
                    array(
                        'dialogId' => '',
                        'dialogTitle' => 'Suppression du Template',
                        'dialogMessage' => 'Vous êtes sur le point de supprimer le template "'.$request->request->get('name').'"<br /><br />Souhaitez-vous continuer ?',
                    )
                );
                return new JsonResponse(
                    array(
                        'dialog' => $response->getContent(),
                        'url' => $this->generateUrl('php_orchestra_cms_bo_jscontextmenudispatcher', array('cmd' => 'deleteTemplate')),
                        'value' => array('templateId' => $request->request->get('templateId')),
                    )
                );
                break;
            case 'deleteTemplate':
                $request->request->add(array('refreshRecord' => true));
                $request->request->add(array('deleted' => true));
                return $this->forward('PHPOrchestraCMSBundle:Template:form', array('templateId' => $request->request->get('templateId')));
                break;
            case 'refresh':
                return $this->forward($request->request->get('controller'));
                break;
            default:
                throw new UnrecognizedCommandTypeException('Unrecognized command type : ' . $cmd);
        }
    }
}