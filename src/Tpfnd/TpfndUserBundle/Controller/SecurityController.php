<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller {

  public function loginAction(Request $request) {
	$authenticationUtils = $this->get('security.authentication_utils');
	$error = $authenticationUtils->getLastAuthenticationError();

	$lastusername = $authenticationUtils->getLastUsername();

	return $this->render(
			'security/login.html.twig',
			array(
				'last_username' => $lastusername,
				'error' => $error,
			)
			);
  }
}
