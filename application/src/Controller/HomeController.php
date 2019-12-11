<?php

namespace App\Controller;

use App\Entity\Platform;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        $em = $this->getDoctrine()->getManager();

        $platformParameters = $em->getRepository(Platform::class)->getPlatformParameters();

        return $this->render('home/home.html.twig', [
         'home_text' => $platformParameters->getHomeText(),
        ]);
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function terms()
    {
        return $this->render('home/terms.html.twig');
    }

    /**
     * @Route("/FAQ", name="faq")
     */
    public function faq()
    {
        return $this->render('home/faq.html.twig');
    }
}
