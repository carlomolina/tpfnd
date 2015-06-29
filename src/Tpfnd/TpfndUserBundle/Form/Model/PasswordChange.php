<?php

namespace Tpfnd\TpfndUserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordChange {

  /**
   * @Assert\NotBlank()
   * @Assert\Length(min=6, max=4096)
   */
  private $oldpassword;

  /**
   * @Assert\NotBlank()
   * @Assert\Length(min=6, max=4096)
   */
  private $newpassword;

  public function setOldpassword($oldpassword) {
	$this->user = $oldpassword;
  }

  public function getOldpassword() {
	return $this->oldpassword;
  }

  public function setNewpassword($newpassword) {
	$this->user = $newpassword;
  }

  public function getNewpassword() {
	return $this->newpassword;
  }

}
