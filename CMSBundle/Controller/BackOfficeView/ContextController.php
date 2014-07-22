<?php
/**
 * This file is part of the PHPOrchestra\CMSBundle.
 *
 * @author Noël Gilain <noel.gilain@businessdecision.com>
 */

namespace PHPOrchestra\CMSBundle\Controller\BackOfficeView;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContextController extends Controller
{
    /**
     * Switch context language
     * 
     * @param string $language
     */
    public function SetLanguageAction($language)
    {
        $contextManager = $this->container->get('php_orchestra_cms.context_manager');
        
        $contextManager->setCurrentLocale($language);
        
        return new JsonResponse(array('success' => true));
    }

    /**
     * Switch context current site
     * 
     * @param string $siteId
     * @param string $siteDomain
     */
    public function SetSiteAction($siteId, $siteDomain)
    {
        $contextManager = $this->container->get('php_orchestra_cms.context_manager');
        
        $contextManager->setCurrentsite($siteId, $siteDomain);
        
        return new JsonResponse(array('success' => true));
    }
}
