<?php

namespace Tpfnd\TpfndUserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tpfnd\TpfndUserBundle\Entity\TpfndUser;

class Registration {

  /**
   * @Assert\Type(type="Tpfnd\TpfndUserBundle\Entity\TpfndUser")
   * @Assert\Valid()
   */
  protected $user;

  /**
   * @Assert\NotBlank()
   * @Assert\True()
   */
  protected $termsAccepted;

  public function setUser(TpfndUser $user) {
	$this->user = $user;
  }

  public function getUser () {
	return $this->user;
  }

  public function getTermsAccepted() {
	return $this->termsAccepted;
  }

  public function setTermsAccepted($termsAccepted) {
	$this->termsAccepted = (bool) $termsAccepted;
  }

}
