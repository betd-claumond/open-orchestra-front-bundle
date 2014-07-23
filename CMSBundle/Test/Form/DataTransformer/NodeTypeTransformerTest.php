<?php

namespace PHPOrchestra\CMSBundle\Test\Form\DataTransformer;

use Phake;
use PHPOrchestra\CMSBundle\Form\DataTransformer\NodeTypeTransformer;

/**
 * Class NodeTypeTransformerTest
 */
class NodeTypeTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeTypeTransformer
     */
    protected $transformer;

    protected $response;
    protected $documentManager;
    protected $displayBlockManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->response = Phake::mock('Symfony\Component\HttpFoundation\Response');
        $this->displayBlockManager = Phake::mock('PHPOrchestra\CMSBundle\DisplayBlock\DisplayBlockManager');
        Phake::when($this->displayBlockManager)->showBack(Phake::anyParameters())->thenReturn($this->response);

        $this->documentManager = Phake::mock('PHPOrchestra\CMSBundle\Document\DocumentManager');

        $this->transformer = new NodeTypeTransformer($this->documentManager, $this->displayBlockManager);
    }

    /**
     * test with load method
     */
    public function testTransformWithLoadMethod()
    {
        $htmlData = 'Some html data';
        Phake::when($this->response)->getContent()->thenReturn($htmlData);


        $areaId = 'testId';
        $blockId = 'blockId';
        $nodeId = 'nodeId';
        $area = array(
            'areaId' => $areaId,
            'classes' => array(),
            'blocks' => array(
                array(
                    'blockId' => $blockId,
                    'nodeId' => $nodeId
                )
            )
        );
        $areas = array($area);

        $block = Phake::mock('PHPOrchestra\CMSBundle\Model\Block');
        $blocks = array($blockId => $block);
        $blocksGroup = Phake::mock('Mandango\Group\EmbeddedGroup');
        Phake::when($blocksGroup)->getSaved()->thenReturn($blocks);
        Phake::when($blocksGroup)->all()->thenReturn($blocks);

        $node = Phake::mock('PHPOrchestra\CMSBundle\Model\Node');
        Phake::when($node)->getAreas()->thenReturn($areas);
        Phake::when($node)->getBlocks()->thenReturn($blocksGroup);

        Phake::when($this->documentManager)->getDocumentById(Phake::anyParameters())->thenReturn($node);

        $returnedNode = $this->transformer->transform($node);

        $this->assertSame($node, $returnedNode);
        Phake::verify($node)->getAreas();
        Phake::verify($node, Phake::times(2))->getBlocks();
        Phake::verify($this->documentManager)->getDocumentById('Node', $nodeId);
        Phake::verify($node)->setAreas(
            json_encode(array(
                'areas' => array(
                    array(
                        'areaId' => $areaId,
                        'classes' => '',
                        'blocks' => array(
                            array(
                                'blockId' => $blockId,
                                'nodeId' => $nodeId,
                                'ui-model' => array(
                                    'label' => $blockId,
                                    'html' => $htmlData,
                                ),
                                'method' => NodeTypeTransformer::BLOCK_LOAD
                            )
                        ),
                        'ui-model' => array('label' => $areaId),
                    )
                )
            ))
        );
    }

    /**
     * Test with generate method
     */
    public function testTransformWithGenerateMethod()
    {
        $htmlData = 'Some html data';
        Phake::when($this->response)->getContent()->thenReturn($htmlData);

        $areaId = 'testId';
        $blockId = 'blockId';
        $nodeId = 0;
        $area = array(
            'areaId' => $areaId,
            'classes' => array(),
            'blocks' => array(
                array(
                    'blockId' => $blockId,
                    'nodeId' => $nodeId,
                )
            )
        );
        $areas = array($area);

        $component = 'Sample';
        $attributNews = 'test';
        $blockRef = Phake::mock('PHPOrchestra\CMSBundle\Model\Block');
        Phake::when($blockRef)->getComponent()->thenReturn($component);
        Phake::when($blockRef)->getAttributes()->thenReturn(array('news' => $attributNews));

        $blocks = array(
            $blockId => $blockRef,
        );

        $blocksGroup = Phake::mock('Mandango\Group\EmbeddedGroup');
        Phake::when($blocksGroup)->getSaved()->thenReturn($blocks);

        $node = Phake::mock('PHPOrchestra\CMSBundle\Model\Node');
        Phake::when($node)->getAreas()->thenReturn($areas);
        Phake::when($node)->getBlocks()->thenReturn($blocksGroup);

        $returnedNode = $this->transformer->transform($node);

        $this->assertSame($node, $returnedNode);
        Phake::verify($node)->getAreas();
        Phake::verify($node)->getBlocks();
        Phake::verify($node)->setAreas(
            json_encode(array(
                'areas' => array(
                    array(
                        'areaId' => $areaId,
                        'classes' => '',
                        'blocks' => array(
                            array(
                                'ui-model' => array(
                                    'label' => $blockId,
                                    'html' => $htmlData,
                                ),
                                'method' => NodeTypeTransformer::BLOCK_GENERATE,
                                'component' => $component,
                                'attributs_news' => $attributNews
                            )
                        ),
                        'ui-model' => array('label' => $areaId),
                    )
                )
            ))
        );
    }
}
