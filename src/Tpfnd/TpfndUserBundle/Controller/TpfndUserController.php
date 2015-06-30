<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tpfnd\TpfndUserBundle\Form\Type\RegistrationType;
use Tpfnd\TpfndUserBundle\Form\Type\EditUserType;
use Tpfnd\TpfndUserBundle\Form\Type\EditPasswordType;
use Tpfnd\TpfndUserBundle\Form\Model\Registration;
use Tpfnd\TpfndUserBundle\Form\Model\PasswordChange;
use Tpfnd\TpfndUserBundle\Entity\TpfndUser;
use Tpfnd\TpfndUserBundle\Entity\TokenLink;

class TpfndUserController extends Controller {

    const PASSWORD_EMAIL_UPDATE_ROUTE = 'password_email_update';

    public function registerAction() {
	$registration = new Registration();
	$form = $this->createForm(new RegistrationType(), $registration, array(
	    'action' => $this->generateUrl('user_create'),
	));

	return $this->render(
			'TpfndUserBundle:TpfndUser:register.html.twig', array(
		    'form' => $form->createView())
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

	    $message = \Swift_Message::newInstance()
		    ->setSubject('tpfnd registration')
		    ->setFrom('carlomanuel@chromedia.com')
		    ->setTo($user->getEmail())
		    ->setContentType("text/html")
		    ->setBody(
		    $this->renderView(
			    'TpfndUserBundle:TpfndUser:notification.html.twig', array(
			'name' => $user->getFirstname()
			    ), 'text/html'
		    ))
	    ;

	    $this->get('mailer')->send($message);

	    return $this->redirectToRoute('tpfnd_home');
	}
    }

    public function editAction($id) {
	$user = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TpfndUser')
		->find($id);
	$form = $this->createForm(new EditUserType(), $user, array(
	    'action' => $this->generateUrl('user_update', array('id' => $id)),
	));
	dump($form->createView());

	return $this->render(
			'TpfndUserBundle:TpfndUser:edit.html.twig', array(
		    'form' => $form->createView())
	);
    }

    public function updateAction(Request $request, $id) {
	$oldUser = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TpfndUser')
		->find($id);

	$form = $this->createForm(new EditUserType(), new TpfndUser());

	$form->handleRequest($request);

	$newUser = $form->getData();

	$em = $this->getDoctrine()->getManager();

	$oldUser->setFirstname($newUser->getFirstname());
	$oldUser->setLastname($newUser->getLastname());

	$em->flush();

	return $this->redirectToRoute('tpfnd_home');
    }

    public function changePasswordAction($id) {
	$form = $this->createForm(new EditPasswordType(), new PasswordChange(), array(
	    'action' => $this->generateUrl('password_update', array('id' => $id)),
	));

	return $this->render(
			'TpfndUserBundle:TpfndUser:edit.html.twig', array(
		    'form' => $form->createView())
	);
    }

    public function updatePasswordAction(Request $request, $id) {
	$user = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TpfndUser')
		->find($id);

	$form = $this->createForm(new EditPasswordType(), new PasswordChange());

	$form->handleRequest($request);

	$passwordChange = $request->get('editPassword');
	if ($this->isPasswordCorrect(
			$user->getPassword(), $passwordChange['oldpassword']
			, $user->getSalt()
		)) {
	    $newPassword = $this->sha256HashPassword(
		    $passwordChange['newpassword']['newpassword'], $user->getSalt()
	    );

	    $em = $this->getDoctrine()->getManager();

	    $user->setPassword($newPassword);

	    $em->flush();

	    return $this->redirectToRoute('tpfnd_home');
	} else {
	    return new Response('Wrong password.');
	}
    }

    public function resetPasswordAction() {
	$form = $this->createFormBuilder(new TpfndUser())
		->setAction($this->generateUrl('process_email'))
		->add('email', 'email')
		->add('save', 'submit', array('label' => 'Submit'))
		->getForm();

	return $this->render('TpfndUserBundle:TpfndUser:resetpassword.html.twig', array(
		    'form' => $form->createView()
	));
    }

    public function processEmailAction(Request $request) {
	$form = $this->createFormBuilder(new TpfndUser())
		->setAction($this->generateUrl('process_email'))
		->add('email', 'email')
		->add('save', 'submit', array('label' => 'Submit'))
		->getForm();
	$form->handleRequest($request);
	$email = $form->getData()->getEmail();


	$user = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TpfndUser')
		->findOneBy(array('email' => $email));
//	dump($user);
//	exit();

	if ($user) {
	    //Send Mail
	    $url = self::PASSWORD_EMAIL_UPDATE_ROUTE;
	    $link = $this->generate24hrLink($user, $url);
	    $message = \Swift_Message::newInstance()
		    ->setSubject('Password Reset')
		    ->setFrom('carlomanuel.molina@chromedia.com')
		    ->setTo($email)
		    ->setContentType("text/html")
		    ->setBody($this->renderView(
			    'TpfndUserBundle:TpfndUser:passwordemail.html.twig', array(
			'user' => $user,
			'link' => $link,
			    ), 'text/html'
		    ))
	    ;
	    $this->get('mailer')->send($message);
	    return new Response('Email was sent for a password reset request.');
	} else {
	    return new Response("We don't have that record in our database.");
	}
    }

    public function passwordEmailUpdateAction($token) {
	//Get Token, compare with date created and new Datetime
	//if within 24hrs, redirect to password_update
	//else response lapsed url
    }

    public function generate24hrLink($user, $url) {
	//create an entry to 24hrlink table
	$em = $this->getDoctrine()->getManager();

	$rawLink = new TokenLink();
	$rawLink->setCreated(new \DateTime());
	$rawLink->setToken(md5(uniqid(rand(), true)));
	$rawLink->setTpfndUser($user);
	$rawLink->setUrl($url);

	$em->persist($rawLink);
	$em->flush();

	return $this->generateUrl($rawLink->getUrl(), array('token' => $rawLink->getToken()));
    }

    public function bcryptHashPassword($password) {
	$options = array('cost' => 11);
	return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function sha256HashPassword($raw, $salt) {
	return hash('sha256', $salt . $raw);
    }

    public function isPasswordCorrect($retrieved, $raw, $salt) {
	return $retrieved === $this->sha256HashPassword($raw, $salt);
    }

}
