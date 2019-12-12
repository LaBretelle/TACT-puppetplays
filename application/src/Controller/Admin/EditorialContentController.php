<?php

namespace App\Controller\Admin;

use App\Entity\EditorialContent;
use App\Form\EditorialContentType;
use App\Service\EditorialContentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/edito", name="admin_edito_") */
class EditorialContentController extends AbstractController
{
    private $em;
    private $editorialContentManager;

    public function __construct(EntityManagerInterface $em, EditorialContentManager $editorialContentManager)
    {
        $this->em = $em;
        $this->editorialContentManager = $editorialContentManager;
    }

    /**
     * @Route("/list", name="list")
     */
    public function list()
    {
        $editos = $this->em->getRepository(EditorialContent::class)->findAll();

        return $this->render('admin/editorial-content/list.html.twig', ['editos' => $editos]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(EditorialContent $editorialContent, Request $request)
    {
        $form = $this->createForm(EditorialContentType::class, $editorialContent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($editorialContent);
            $this->em->flush();

            return $this->redirectToRoute('admin_edito_list');
        }

        return $this->render('admin/editorial-content/edit.html.twig', ['form' => $form->createView()]);
    }
}
