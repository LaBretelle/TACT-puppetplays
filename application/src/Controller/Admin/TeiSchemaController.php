<?php

namespace App\Controller\Admin;

use App\Entity\TeiSchema;
use App\Form\TeiSchemaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/schema", name="admin_schema_") */
class TeiSchemaController extends Controller
{


    /**
     * @Route("/{id}", name="manage", defaults={"id"=null}, requirements={"id"="\d+"})
     */
    public function manage(TeiSchema $schema = null, Request $request)
    {
        $schema = $schema ? $schema : new TeiSchema();
        $form = $this->createForm(TeiSchemaType::class, $schema);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($schema);
            $em->flush();
            return $this->redirectToRoute('admin_schema_list');
        }

        return $this->render(
            'admin/schema/manage.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/list", name="list")
     */
    public function list(Request $request)
    {
        $schemas = $this->getDoctrine()->getRepository(TeiSchema::class)->findAll();

        return $this->render(
            'admin/schema/list.html.twig',
            ['schemas' => $schemas]
        );
    }
}
