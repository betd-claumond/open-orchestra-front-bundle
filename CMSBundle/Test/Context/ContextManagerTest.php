<?php

/*
 * Business & Decision - Commercial License
 *
 * Copyright 2014 Business & Decision.
 *
 * All rights reserved. You CANNOT use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell this Software or any parts of this
 * Software, without the written authorization of Business & Decision.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * See LICENSE.txt file for the full LICENSE text.
 */

namespace PHPOrchestra\CMSBundle\Test\Context;

use PHPOrchestra\CMSBundle\Context\ContextManager;
use PHPOrchestra\CMSBundle\Test\Mock\SessionManager;
use PHPOrchestra\CMSBundle\Test\Mock\Site;
use Phake;

/**
 * Unit tests of contextManager
 *
 * @author Noël GILAIN <noel.gilain@businessdecision.com>
 */
class ContextManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $sessionManager;
    protected $contextManager;
    protected $documentManager;
    
    /**
     * Tests setup
     */
    public function setUp()
    {
        $this->sessionManager = new SessionManager();
        $this->documentManager = Phake::mock('PHPOrchestra\CMSBundle\Document\DocumentManager');
        $this->contextManager = new ContextManager($this->sessionManager, $this->documentManager);
    }


    /**
     * @param string $locale
     * 
     * @dataProvider getLocale
     */
    public function testGetCurrentLocale($locale)
    {
        $this->sessionManager->set('_locale', $locale);
        $this->assertEquals($locale, $this->contextManager->getCurrentLocale());
    }


    /**
     * @param string $locale
     * 
     * @dataProvider getLocale
     */
    public function testSetCurrentLocale($locale)
    {
        $this->contextManager->setCurrentLocale($locale);
        $this->assertEquals($locale, $this->sessionManager->get('_locale'));
    }


    /**
     * @param array $documentsList
     * @param array $expectedArray
     *  
     * @dataProvider getAvailableSites
     */
    public function testGetAvailableSites($documentsList, $expectedArray)
    {
        Phake::when($this->documentManager)->getDocuments(Phake::anyParameters())->thenReturn($documentsList);
        
        $this->assertEquals($expectedArray, $this->contextManager->getAvailableSites());
    }


    /**
     * @param array $site
     * 
     * @dataProvider getSite
     */
    public function testSetCurrentSite($site)
    {
        $this->contextManager->setCurrentSite($site['id'], $site['domain']);
        $this->assertEquals($site, $this->sessionManager->get('_site'));
    }


    /**
     * @param array $site
     * 
     * @dataProvider getSite
     */
    public function testGetCurrentSite($site)
    {
        $this->sessionManager->set('_site', $site);
        $this->assertEquals($site, $this->contextManager->getCurrentSite());
    }


    /**
     * Locale provider
     */
    public function getLocale()
    {
        return array(
            array(''),
            array('fr'),
            array(3),
            array('fakeKey' => 'fakeValue')
        );
    }


    /**
     * Site provider
     */
    public function getSite()
    {
        return array(
            array(array('id' => 'fakeId', 'domain' => 'fakeDomain'))
        );
    }


    /**
     * Available sites provider
     */
    public function getAvailableSites()
    {
        $site1 = new Site('site1');
        $site2 = new Site('site2');
        
        return array(
            array(
                array($site1, $site2),
                array(
                    array('id' => $site1->getId(), 'domain' => $site1->getDomain()),
                    array('id' => $site2->getId(), 'domain' => $site2->getDomain())
                )
            )
        );
    }
}
