<?php

namespace Tpfnd\TpfndUserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class RegistrationEmailNotificationEvent extends Event {

    private $user;
    private $link;

    public function __construct($user, $link) {
        $this->user = $user;
        $this->link = $link;
    }

    public function getUser() {
        return $this->user;
    }

    public function getLink() {
        return $this->link;
    }
}
