<?php

namespace App\Controller\Admin;

use App\Entity\Website;
use App\Service\WebsiteManager;
use App\Form\WebsiteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/website", name="admin_website_") */
class WebsiteController extends Controller
{
    private $websiteManager;

    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * @Route("/", name="properties")
     */
    public function setWebsiteProperties(Request $request)
    {
        $site = $this->getDoctrine()->getRepository(Website::class)->findAll()[0];
        $form = $this->createForm(WebsiteType::class, $site);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->websiteManager->updateWebsiteProperties($site);
            return $this->redirectToRoute('home');
        }
        return $this->render(
            'admin/site/properties.html.twig',
            ['form' => $form->createView()]
        );
    }
}
