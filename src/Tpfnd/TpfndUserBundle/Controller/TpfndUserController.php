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
use Doctrine\ORM\ORMException;

class TpfndUserController extends Controller {

    const EMAIL_LINK_ROUTE = 'check_token';
    const EMAIL_PASSWORD_RESET_ROUTE = 'password_email_change';

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
	    try {
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
	    } catch (\Exception $e) {
		//TODO (proper error handling)
		return new Response($this->createNotFoundException());
	    }

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
	$loggedUser = $this->get('security.token_storage')->getToken()->getUser();
	if ($loggedUser->getId() != $id) {
	    return new Response("You are not allowed to edit somebody else's user details.");
	}
	try {
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
	} catch (ORMException $e) {
	    //TODO (Proper error handling?)
	    return new Response($e);
	}


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
	$loggedUser = $this->get('security.token_storage')->getToken()->getUser();
	if ($loggedUser->getId() != $id) {
	    return new Response("You are not allowed to edit somebody else's user details.");
	}
	$user = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TpfndUser')
		->find($id);

	return $this->proceedWithPasswordChange($request, $user);
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

	if ($user) {
	    //Send Mail
	    $url = self::EMAIL_LINK_ROUTE;
	    $link = "http://" . $this->generate24hrLink($user, $url);
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

    public function checkTokenAction($token) {
	$tokenLink = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TokenLink')
		->findOneBy(array('token' => $token));

	//TODO Check proper way of calculating time difference
	//Have this in hours
	$timeLapsed = time_diff($tokenLink->getCreated(), new \DateTime());

	if ($timeLapsed < 24) {
	    return $this->redirect($this->generateUrl(self::EMAIL_PASSWORD_RESET_ROUTE));
	} else {
	    return new Response('The 24-hour period has already lapsed.');
	}
    }

    public function changePasswordFromEmailAction($token) {
	$form = $this->createForm(new EditPasswordType(), new PasswordChange(), array(
	    'action' => $this->generateUrl('password_update_from_email', array('token' => $token)),
	));

	return $this->render(
			'TpfndUserBundle:TpfndUser:edit.html.twig', array(
		    'form' => $form->createView())
	);
    }

    public function updatePasswordFromEmailAction(Request $request, $token) {
	//TODO (Get user from left join)
	$user = $this->getDoctrine()
		->getRepository('TpfndUserBundle:TokenLink')
		->findOneBy($token);

	return $this->proceedWithPasswordChange($request, $user);
    }

    public function proceedWithPasswordChange(Request $request, $user) {
	$form = $this->createForm(new EditPasswordType(), new PasswordChange());

	$form->handleRequest($request);

	$passwordChange = $request->get('editPassword');
	if ($this->isPasswordCorrect(
			$user->getPassword(), $passwordChange['oldpassword']
			, $user->getSalt()
		)) {
	    try {
		$newPassword = $this->sha256HashPassword(
			$passwordChange['newpassword']['newpassword'], $user->getSalt()
		);

		$em = $this->getDoctrine()->getManager();

		$user->setPassword($newPassword);

		$em->flush();
	    } catch (ORMException $e) {
		//TODO (proper error handling)
		return new Response($e);
	    }

	    return $this->redirectToRoute('tpfnd_home');
	} else {
	    return new Response('Wrong password.');
	}
    }

    public function generate24hrLink($user, $url) {
	try {
	    $em = $this->getDoctrine()->getManager();

	    $rawLink = new TokenLink();
	    $rawLink->setCreated(new \DateTime());
	    $rawLink->setToken(md5(uniqid(rand(), true)));
	    $rawLink->setTpfndUser($user);
	    $rawLink->setUrl($url);

	    $em->persist($rawLink);
	    $em->flush();
	} catch (ORMException $e) {
	    //TODO (proper error handling)
	    return new Response($e);
	}

	return $this->generateUrl($rawLink->getUrl(), array('token' => $rawLink->getToken()));
    }

    public function sha256HashPassword($raw, $salt) {
	return hash('sha256', $salt . $raw);
    }

    public function isPasswordCorrect($retrieved, $raw, $salt) {
	return $retrieved === $this->sha256HashPassword($raw, $salt);
    }

}
