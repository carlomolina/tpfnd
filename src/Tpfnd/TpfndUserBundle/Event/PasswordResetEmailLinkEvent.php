<?php

namespace Tpfnd\TpfndUserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class PasswordResetEmailLinkEvent extends Event
{
    private $user;
    private $link;
    private $email;

    public function __construct($user, $link, $email)
    {
        $this->user = $user;
        $this->link = $link;
        $this->email = $email;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getEmail()
    {
        return $this->email;
    }
}