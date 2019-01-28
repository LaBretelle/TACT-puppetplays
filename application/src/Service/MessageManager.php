<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\Recipient;
use App\Entity\User;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class MessageManager
{
    protected $em;
    protected $security;
    protected $currentUser;
    protected $fm;

    public function __construct(EntityManagerInterface $em, Security $security, FlashManager $fm)
    {
        $this->em = $em;
        $this->security = $security;
        $this->currentUser = $this->security->getUser();
        $this->fm = $fm;
    }

    public function create($recipients, $content, $flush = true)
    {
        $message = new Message;
        $message->setContent($content);
        $this->em->persist($message);

        foreach ($recipients as $recipient) {
            $r = new Recipient;
            $r->setUser($recipient);
            $r->setViewed(false);
            $r->setMessage($message);
            $this->em->persist($r);
        }
        
        if ($flush) {
            $this->em->flush();
        }

        return;
    }

    public function delete($recipients)
    {
        foreach ($recipients as $recipient) {
            $this->em->remove($recipient);
            $this->em->flush();
        }

        return;
    }

    public function deleteOne(Recipient $recipient)
    {
        if ($this->currentUser == $recipient->getUser()) {
            $this->delete([$recipient]);
            $this->fm->add('notice', 'message_deleted');
        }

        return;
    }

    public function deleteAll()
    {
        $messages = $this->getUserMessages();
        $this->delete($messages);
        $this->fm->add('notice', 'messages_deleted');

        return;
    }

    public function deleteRead()
    {
        $messages = $this->getRead();
        $this->delete($messages);
        $this->fm->add('notice', 'messages_delete_read_done');

        return;
    }

    public function setAsread($recipients)
    {
        foreach ($recipients as $recipient) {
            $recipient->setViewed(true);
            $this->em->persist($recipient);
        }
        $this->em->flush();

        return;
    }

    public function setAllAsRead()
    {
        $messages = $this->getUnread();
        $this->setAsRead($messages);
        $this->fm->add('notice', 'messages_set_as_read_done');

        return;
    }

    public function setOneAsRead(Recipient $recipient)
    {
        if ($this->currentUser == $recipient->getUser()) {
            $this->setAsRead([$recipient]);
            $this->fm->add('notice', 'message_set_as_read_done');
        }

        return;
    }


    public function getRead()
    {
        $messages = $this->em->getRepository("App:Recipient")->findBy([
            'user' => $this->currentUser,
            'viewed' => true
        ]);

        return $messages;
    }


    public function getUserMessages()
    {
        $messages = $this->em->getRepository("App:Recipient")->findByUser($this->currentUser);

        return $messages;
    }

    public function getUnread()
    {
        $messages = $this->em->getRepository("App:Recipient")->findBy([
            'user' => $this->currentUser,
            'viewed' => false
        ]);

        return $messages;
    }
}
