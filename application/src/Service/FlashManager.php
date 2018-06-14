<?php

namespace App\Service;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashManager
{
    private $translator;
    private $session;

    public function __construct(TranslatorInterface $translator, SessionInterface $session)
    {
        $this->translator = $translator;
        $this->session = $session;
    }

    public function add($label, $message, $args = [])
    {
        $this->session->getFlashBag()->add($label, $this->translator->trans($message, $args));

        return;
    }
}
