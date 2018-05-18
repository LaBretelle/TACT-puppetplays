<?php

namespace App\Controller;

use App\Entity\Project;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{


    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository(Project::class)->findAll();

        $homeText = "Lorem <b>ipsum</b> dolor sit amet, consectetur adipiscing elit. Phasellus tincidunt viverra turpis nec porttitor. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque fermentum ipsum in lectus imperdiet, et efficitur nulla fringilla. Etiam quam arcu, dignissim non convallis eget, pretium ac metus. Suspendisse pretium augue nec sollicitudin luctus. Phasellus blandit, nulla et sodales ornare, urna ex venenatis ligula, id vehicula est nunc et mi. Vivamus at arcu convallis, rhoncus nunc eu, facilisis metus. Nulla nec sagittis nulla, vitae pulvinar lorem. Vivamus id odio a velit tristique pulvinar. Suspendisse vulputate orci in enim aliquet condimentum. Nunc mattis sem eget nibh venenatis vulputate. Proin eu nisi metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur arcu ipsum, accumsan sit amet leo ut, mollis consectetur urna. Mauris mollis ac magna vitae posuere. Phasellus vel ultricies quam.";

        return $this->render('home/home.html.twig', [
         'projects' => $projects,
         'home_text' => $homeText,
        ]);
    }
}
