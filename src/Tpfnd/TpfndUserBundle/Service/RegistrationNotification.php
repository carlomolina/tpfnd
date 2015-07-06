<?php

namespace Tpfnd\TpfndUserBundle\Service;

use Tpfnd\TpfndUserBundle\Event\RegistrationEmailNotificationEvent;

class RegistrationNotification
{
    private $twig;
    private $mailer;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function onRegistrationEmailNotificationEvent(RegistrationEmailNotificationEvent $event)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('tpfnd registration')
            ->setFrom('carlomanuel@chromedia.com')
            ->setTo($event->getUser()->getEmail())
            ->setContentType("text/html")
            ->setBody(
                $this->twig->render(
                    'TpfndUserBundle:TpfndUser:notification.html.twig', array(
                    'name' => $event->getUser()->getFirstname(),
                    'link' => $event->getLink()
                ), 'text/html'
                ));

        $this->mailer->send($message);
    }

}
