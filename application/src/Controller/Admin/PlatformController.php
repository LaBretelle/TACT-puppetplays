<?php

namespace App\Controller\Admin;

use App\Entity\Platform;
use App\Service\PlatformManager;
use App\Form\PlatformType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/platform", name="admin_platform_") */
class PlatformController extends AbstractController
{
    private $manager;

    public function __construct(PlatformManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="properties")
     */
    public function setPlatformProperties(Request $request)
    {
        $platform = $this->getDoctrine()->getRepository(Platform::class)->getPlatformParameters();
        $form = $this->createForm(PlatformType::class, $platform);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->updatePlatformProperties($platform);
            $logo = $form->get('logo')->getData();
            $previous_logo = $request->get('previous_logo');
            $this->manager->handleLogo($platform, $logo, $previous_logo);

            $platformGuide = $form->get('platform_guide')->getData();
            $managerGuide = $form->get('manager_guide')->getData();
            $this->manager->handleGuide($platform, $platformGuide, $managerGuide);

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'admin/platform/properties.html.twig',
            ['form' => $form->createView(), 'platform' => $platform]
        );
    }

    /**
     * @Route("/logo/delete", name="logo_delete", options={"expose"=true}, methods="DELETE")
     */
    public function deletePlatformLogo(Request $request)
    {
        $platform = $this->getDoctrine()->getRepository(Platform::class)->getPlatformParameters();
        $this->manager->deleteLogo($platform);

        return $this->json([], $status = 200);
    }
}
