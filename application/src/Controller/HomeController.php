<?php

namespace App\Controller;

use App\Entity\Platform;
use App\Entity\EditorialContent;
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
        return $this->render('home/home.html.twig');
    }

    /**
     * @Route("/actu", name="actu")
     */
    public function actu()
    {
        $em = $this->getDoctrine()->getManager();
        $actu = $em->getRepository(EditorialContent::class)->findOneByName("actualités");

        return $this->render('home/actu.html.twig', [
         'actu' => $actu
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
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('home/about.html.twig');
    }
}
