<?php

namespace App\Controller;

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
        $rss = simplexml_load_file('https://icima.hypotheses.org/feed');
/* mettre le feed rss du site PuppetPlays quand ça sera rempli : https://www.google.com/alerts/feeds/11038047740276319145/12529615087899373986   */
        return $this->render('home/actu.html.twig', ['rss' => $rss]);
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
