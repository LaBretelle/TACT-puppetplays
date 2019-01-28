<?php

namespace App\Controller;

use App\Entity\Recipient;
use App\Service\MessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/message", name="message_")
 */
class MessageController extends AbstractController
{
    protected $em;
    protected $messageManager;

    public function __construct(EntityManagerInterface $em, MessageManager $messageManager)
    {
        $this->em = $em;
        $this->messageManager = $messageManager;
    }

    /**
     * @Route("/list", name="list")
     */
    public function list()
    {
        $messages = $this->messageManager->getUserMessages();

        return $this->render('message/list.html.twig', ['messages' => $messages]);
    }

    /**
     * @Route("/{id}/delete", name="delete_one")
     */
    public function delete(Recipient $recipient)
    {
        $this->messageManager->deleteOne($recipient);

        return $this->redirectToRoute('message_list');
    }

    /**
     * @Route("/delete-all", name="delete_all")
     */
    public function deleteAll()
    {
        $this->messageManager->deleteAll();

        return $this->redirectToRoute('message_list');
    }

    /**
     * @Route("/delete-read", name="delete_read")
     */
    public function deleteRead()
    {
        $this->messageManager->deleteRead();

        return $this->redirectToRoute('message_list');
    }

    /**
     * @Route("/{id}/read", name="read_one")
     */
    public function read(Recipient $recipient)
    {
        $this->messageManager->setOneAsRead($recipient);

        return $this->redirectToRoute('message_list');
    }

    /**
     * @Route("/read-all", name="read_all")
     */
    public function readAll()
    {
        $this->messageManager->setAllAsRead();

        return $this->redirectToRoute('message_list');
    }
}
