<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller {

  public function loginAction(Request $request) {
	$authenticationUtils = $this->get('security.authentication_utils');
	$error = $authenticationUtils->getLastAuthenticationError();

	#username for this use case is the user's email, as per security settings
	$lastUsername = $authenticationUtils->getLastUsername();

	return $this->render(
			'security/login.html.twig',
			array(
				'last_email' => $lastUsername,
				'error' => $error,
			)
			);
  }
}
