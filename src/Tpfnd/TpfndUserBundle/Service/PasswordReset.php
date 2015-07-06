<?php

namespace Tpfnd\TpfndUserBundle\Service;

use Tpfnd\TpfndUserBundle\Event\PasswordResetEmailLinkEvent;

class PasswordReset
{
    private $twig;
    private $mailer;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function onPasswordResetEmailLinkEvent(PasswordResetEmailLinkEvent $event)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Password Reset')
            ->setFrom('carlomanuel.molina@chromedia.com')
            ->setTo($event->getUser()->getEmail())
            ->setContentType("text/html")
            ->setBody(
                $this->twig->render(
                    'TpfndUserBundle:TpfndUser:passwordemail.html.twig', array(
                    'user' => $event->getUser(),
                    'link' => $event->getLink(),
                ), 'text/html'
                ));

        $this->mailer->send($message);

    }

}
