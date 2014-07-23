<?php
/**
 * This file is part of the PHPOrchestra\CMSBundle.
 *
 * @author Noël Gilain <noel.gilain@businessdecision.com>
 */

namespace PHPOrchestra\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PHPOrchestra\CMSBundle\Model\Area;
use PHPOrchestra\CMSBundle\Model\Node;
use PHPOrchestra\CMSBundle\Exception\NonExistingDocumentException;
use Symfony\Component\HttpFoundation\Request;
use PHPOrchestra\CMSBundle\Form\Type\NodeType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PHPOrchestra\CMSBundle\Helper\NodeHelper;

class NodeController extends Controller
{
    
    /**
     * Cache containing blocks potentially used in current node.
     * This cache contains all blocks defined in nodes that are mentionned
     * in block references of the current node.
     * This is to prevent multiple loading of the same node document
     * when same external node is linked several times in the current node
     * 
     * @var Array
     */
    private $externalBlocks = array();
    
    
    /**
     * Contains blocks used in the current node,
     * either defined in current or external node
     * 
     * @var Array
     */
    private $blocks = array();
    
    
    
    /**
     * A getter for the variable externalBlocks
     *
     * @param none
     */
    public function getExternalBlocks()
    {
        return $this->externalBlocks;
    }
    
    /**
     * A getter for the variable externalBlocks
     *
     * @param none
     */
    public function getBlocksNoparam()
    {
        return $this->blocks;
    }
    
    /**
     * Render Node
     * 
     * @param int $nodeId
     * @return Response
     */
    public function showAction($nodeId)
    {
        $node = $this->get('php_orchestra_cms.document_manager')->getDocument('Node', array('nodeId' => $nodeId));
        if (is_null($node)) {
            throw new NonExistingDocumentException("Node not found");
        }

        $response = $this->render(
            'PHPOrchestraCMSBundle:Node:show.html.twig',
            array(
                'node' => $node,
                'datetime' => time()
            )
        );
        
        $response->setPublic();
        $response->setSharedMaxAge(100);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        return $response;
    }

    /**
     * @param Request $request
     * @param int $nodeId
     *
     * @return JsonResponse|Response
     */
    public function formAction(Request $request, $nodeId = 0)
    {
        $documentManager = $this->container->get('php_orchestra_cms.document_manager');

        if (empty($nodeId)) {
            $node = $documentManager->createDocument('Node');
            $node->setSiteId(1);
            $node->setLanguage('fr');
        } else {
            $node = $documentManager->getDocument(
                'Node',
                array('nodeId' => $nodeId)
            );
            $node->setVersion($node->getVersion() + 1);
        }

        if ($request->request->get('refreshRecord')) {
            $node->fromArray($request->request->all());
        } else {
            $form = $this->createForm(
                'node',
                $node,
                array(
                    'inDialog' => true,
                    'beginJs' => array('pagegenerator/dialogNode.js', 'pagegenerator/model.js'),
                    'endJs' => array('pagegenerator/node.js?'.time()),
                    'action' => $request->getUri()
                )
            );
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                $node = $form->getData();
            }
        }

        if ($this->get('validator')->validate($node)) {
           $response['dialog'] = $this->render(
                'PHPOrchestraCMSBundle:BackOffice/Dialogs:confirmation.html.twig',
                array(
                    'dialogId' => '',
                    'dialogTitle' => 'Modification du node',
                    'dialogMessage' => 'Modification ok',
                )
            )->getContent();
            if (!$node->getDeleted()) {
                $node->setId(null);
                $node->setIsNew(true);
                $node->save();

                /*$indexManager = $this->get('php_orchestra_indexation.indexer_manager');
                $indexManager->index($node, 'Node');*/
            } else {
                $this->deleteTree($node->getNodeId());
                $response['redirect'] = $this->generateUrl('php_orchestra_cms_bo_edito');
            }
            return new JsonResponse($response);
        }
        return $this->render(
            'PHPOrchestraCMSBundle:BackOffice/Editorial:template.html.twig',
            array(
                'mainTitle' => 'Gestion des pages',
                'tableTitle' => '',
                'form' => $form->createView()
            )
        );
    }

    /**
     * Recursivly delete a tree
     * 
     * @param string $nodeId
     */
    protected function deleteTree($nodeId)
    {
        /*$indexManager = $this->get('php_orchestra_indexation.indexer_manager');
          $indexManager->deleteIndex($nodeId);*/
        
        $documentManager = $this->get('php_orchestra_cms.document_manager');
        
        $nodeVersions = $documentManager->getDocuments('Node', array('nodeId' => $nodeId));
        
        foreach ($nodeVersions as $node) {
            $node->markAsDeleted();
        };
        
        $sons = $documentManager->getNodeSons($nodeId);
        
        foreach ($sons as $son) {
            $this->deleteTree($son['_id']);
        }
        return true;
    }
}
