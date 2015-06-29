<?php

namespace Tpfnd\TpfndUserBundle\Service;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Tpfnd\TpfndUserBundle\Service\Utils\TpfndSecurity;

class TpfndAuthenticationUtils extends AuthenticationUtils {

  /**
   * @return string
   */
  public function getLastEmail() {
	$session = $this->getRequest()->getSession();

	return null === $session ? '' : $session->get(TpfndSecurity::LAST_EMAIL);
  }

}
