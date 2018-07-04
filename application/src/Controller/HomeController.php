<?php

namespace App\Controller;

use App\Entity\Platform;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository(Project::class)->findBy([
          'deleted' => false
        ]);

        $platformParameters = $em->getRepository(Platform::class)->getPlatformParameters();
        return $this->render('home/home.html.twig', [
         'projects' => $projects,
         'home_text' => $platformParameters->getHomeText(),
        ]);
    }
}
