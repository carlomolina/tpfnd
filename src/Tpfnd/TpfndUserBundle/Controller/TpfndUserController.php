<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tpfnd\TpfndUserBundle\Form\Type\RegistrationType;
use Tpfnd\TpfndUserBundle\Form\Model\Registration;

class TpfndUserController extends Controller {

  public function registerAction() {
	$registration = new Registration();
	$form = $this->createForm(new RegistrationType(), $registration, array(
		'action' => $this->generateUrl('user_create'),
	));

	return $this->render(
					'TpfndUserBundle:TpfndUser:register.html.twig', array('form' => $form->createView())
	);
  }

  public function createAction(Request $request) {
	$em = $this->getDoctrine()->getManager();

	$form = $this->createForm(new RegistrationType(), new Registration());

	$form->handleRequest($request);

	if ($form->isValid()) {
	  $registration = $form->getData();
	  $user = $registration->getUser();
	  $user->setSalt(uniqid(mt_rand()));
	  $name = str_replace(' ', '', $user->getFirstname() . $user->getLastname());
	  $user->setUsername(strtolower($name));

	  $encoder = $this->container->get('sha256salted_encoder');
	  $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
	  $user->setPassword($password);

	  $em->persist($user);
	  $em->flush();

	  return $this->redirectToRoute('homepage');
	}
  }

  public function bcryptHashPassword($password) {
	$options = array('cost' => 11);
	return password_hash($password, PASSWORD_BCRYPT, $options);
  }

}
