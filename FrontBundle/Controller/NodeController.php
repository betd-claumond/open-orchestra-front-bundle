<?php

namespace PHPOrchestra\FrontBundle\Controller;

use PHPOrchestra\FrontBundle\Exception\NonExistingDocumentException;
use PHPOrchestra\ModelBundle\Model\NodeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

/**
 * Class NodeController
 */
class NodeController extends Controller
{
    /**
     * Render Node
     *
     * @param int $nodeId
     *
     * @Config\Route("/node/{nodeId}", name="php_orchestra_front_node")
     * @Config\Method({"GET"})
     *
     * @throws NonExistingDocumentException
     * @return Response
     */
    public function showAction($nodeId)
    {
        $node = $this->get('php_orchestra_model.repository.node')->findOneByNodeId($nodeId);
        if (is_null($node)) {
            throw new NonExistingDocumentException();
        }

        $response = $this->render(
            'PHPOrchestraFrontBundle:Node:show.html.twig',
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
}
