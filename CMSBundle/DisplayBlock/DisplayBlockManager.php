<?php

namespace PHPOrchestra\CMSBundle\DisplayBlock;

use PHPOrchestra\CMSBundle\Exception\DisplayBlockStrategyNotFoundException;
use PHPOrchestra\CMSBundle\Model\Block;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DisplayBlockManager
 */
class DisplayBlockManager
{
    protected $strategies = array();
    protected $templating;

    /**
     * @param TimedTwigEngine $templating
     */
    public function __construct(TimedTwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param DisplayBlockInterface $strategy
     */
    public function addStrategy(DisplayBlockInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
        $strategy->setManager($this);
    }

    /**
     * Perform the show action for a block
     *
     * @param Block $block
     *
     * @throws DisplayBlockStrategyNotFoundException
     * @return Response
     */
    public function show(Block $block)
    {
        /** @var DisplayBlockInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($block)) {
                return $strategy->show($block);
            }
        }
        throw new DisplayBlockStrategyNotFoundException();
    }

    /**
     * Perform the show action for a block on the backend
     *
     * @param Block $block
     *
     * @throws DisplayBlockStrategyNotFoundException
     * @return Response
     */
    public function showBack(Block $block)
    {
        /** @var DisplayBlockInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($block)) {
                return $strategy->showBack($block);
            }
        }
        throw new DisplayBlockStrategyNotFoundException();
    }

    /**
     * @return \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
     */
    public function getTemplating()
    {
        return $this->templating;
    }
}