<?php

namespace PHPOrchestra\ApiBundle\Transformer;

use PHPOrchestra\ApiBundle\Facade\AreaFacade;
use PHPOrchestra\ApiBundle\Facade\FacadeInterface;
use PHPOrchestra\ModelBundle\Model\AreaInterface;
use PHPOrchestra\ModelBundle\Model\NodeInterface;
use PHPOrchestra\ModelBundle\Repository\NodeRepository;

/**
 * Class AreaTransformer
 */
class AreaTransformer extends AbstractTransformer
{
    protected $nodeRepository;

    /**
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param AreaInterface $mixed
     * @param NodeInterface $node
     *
     * @return FacadeInterface
     */
    public function transform($mixed, NodeInterface $node = null)
    {
        $facade = new AreaFacade();

        $facade->areaId = $mixed->getAreaId();
        $facade->classes = implode(',', $mixed->getClasses());
        foreach ($mixed->getSubAreas() as $subArea) {
            $facade->addArea($this->getTransformer('area')->transform($subArea, $node));
        }
        foreach ($mixed->getBlocks() as $block) {
            if (0 == $block['nodeId']) {
                $facade->addBlock($this->getTransformer('block')->transform($node->getBlocks()->get($block['blockId'])));
            } else {
                $node = $this->nodeRepository->findOneByNodeId($block['nodeId']);
                $facade->addBlock($this->getTransformer('block')->transform(
                    $node->getBlocks()->get($block['blockId']),
                    false
                ));
            }
        }
        $facade->uiModel = $this->getTransformer('ui_model')->transform(array('label' => $mixed->getAreaId()));

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'area';
    }
}
