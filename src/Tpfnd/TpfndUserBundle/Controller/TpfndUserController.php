<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tpfnd\TpfndUserBundle\Form\Type\RegistrationType;
use Tpfnd\TpfndUserBundle\Form\Type\EditUserType;
use Tpfnd\TpfndUserBundle\Form\Type\EditPasswordType;
use Tpfnd\TpfndUserBundle\Form\Type\ResetPasswordType;
use Tpfnd\TpfndUserBundle\Form\Model\Registration;
use Tpfnd\TpfndUserBundle\Form\Model\PasswordChange;
use Tpfnd\TpfndUserBundle\Entity\TpfndUser;
use Tpfnd\TpfndUserBundle\Entity\TokenLink;
use Doctrine\ORM\ORMException;

class TpfndUserController extends Controller
{

    const URL_LINK_PREPEND = "http://localhost:8000";

    public function registerAction()
    {
        $registration = new Registration();
        $form = $this->createForm(new RegistrationType(), $registration, array(
            'action' => $this->generateUrl('user_create'),
        ));

        return $this->render(
            'TpfndUserBundle:TpfndUser:register.html.twig', array(
                'form' => $form->createView())
        );
    }

    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RegistrationType(), new Registration(), array(
            'action' => $this->generateUrl('user_create')));

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
            $em->persist($form->getData()->getUser());
            $em->flush();

            $url = 'registration_email_confirmation';
            $link = self::URL_LINK_PREPEND . $this->generateLink($user, $url);

            $message = \Swift_Message::newInstance()
                ->setSubject('tpfnd registration')
                ->setFrom('carlomanuel@chromedia.com')
                ->setTo($user->getEmail())
                ->setContentType("text/html")
                ->setBody(
                    $this->renderView(
                        'TpfndUserBundle:TpfndUser:notification.html.twig', array(
                        'name' => $user->getFirstname(),
                        'link' => $link
                    ), 'text/html'
                    ));

            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add('notice', 'Successfully registered to tpfnd.
            An email has been sent for confirmation.');

            return $this->redirectToRoute('tpfnd_home');
        } else {
            return $this->render(
                'TpfndUserBundle:TpfndUser:register.html.twig', array(
                    'form' => $form->createView())
            );
        }
    }

    public function editAction($id)
    {
        $loggedUser = $this->get('security.token_storage')->getToken()->getUser();
        if ($loggedUser->getId() != $id) {
            return new Response("You are not allowed to edit somebody else's user details.");
        }

        try {
            $user = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TpfndUser')
                ->find($id);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        $form = $this->createForm(new EditUserType(), $user, array(
            'action' => $this->generateUrl('user_update', array('id' => $id)),
        ));

        return $this->render(
            'TpfndUserBundle:TpfndUser:edit.html.twig', array(
                'form' => $form->createView())
        );
    }

    public function updateAction(Request $request, $id)
    {
        try {
            $oldUser = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TpfndUser')
                ->find($id);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        $form = $this->createForm(new EditUserType(), new TpfndUser());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $newUser = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $oldUser->setFirstname($newUser->getFirstname());
            $oldUser->setLastname($newUser->getLastname());

            $em->flush();

            $this->get('session')->getFlashBag()->add('notice', 'Successfully updated user details.');

            return $this->redirectToRoute('tpfnd_home');
        } else {
            return $this->render(
                'TpfndUserBundle:TpfndUser:edit.html.twig', array(
                    'form' => $form->createView())
            );
        }
    }

    public function changePasswordAction($id)
    {
        $loggedUser = $this->get('security.token_storage')->getToken()->getUser();
        if ($loggedUser->getId() != $id) {
            return new Response("You are not allowed to edit somebody else's user details.");
        }

        $form = $this->createForm(new EditPasswordType(), new PasswordChange(), array(
            'action' => $this->generateUrl('password_update', array('id' => $id)),
        ));


        return $this->render(
            'TpfndUserBundle:TpfndUser:edit.html.twig', array(
                'form' => $form->createView())
        );
    }

    public
    function updatePasswordAction(Request $request, $id)
    {
        try {
            $user = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TpfndUser')
                ->find($id);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        $form = $this->createForm(new EditPasswordType(), new PasswordChange());

        $form->handleRequest($request);

        $passwordChange = $request->get('editPassword');

        if ($this->isPasswordCorrect(
            $user->getPassword(), $passwordChange['oldpassword']
            , $user->getSalt())
        ) {
            $newPassword = $this->sha256HashPassword(
                $passwordChange['newpassword']['newpassword'], $user->getSalt()
            );
            return $this->proceedWithPasswordChange($newPassword, $user);
        } else {
            return new Response("You have entered an incorrect password.");
        }

    }

    public
    function resetPasswordAction()
    {
        $form = $this->createFormBuilder(new TpfndUser())
            ->setAction($this->generateUrl('password_email_process'))
            ->add('email', 'email')
            ->add('save', 'submit', array('label' => 'Submit'))
            ->getForm();

        return $this->render('TpfndUserBundle:TpfndUser:resetpassword.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public
    function processEmailAction(Request $request)
    {
        $form = $this->createFormBuilder(new TpfndUser())
            ->setAction($this->generateUrl('password_email_process'))
            ->add('email', 'email')
            ->add('save', 'submit', array('label' => 'Submit'))
            ->getForm();
        $form->handleRequest($request);
        $email = $form->getData()->getEmail();

        try {
            $user = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TpfndUser')
                ->findOneByEmail($email);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        if ($user) {
            //Send Mail
            $url = 'password_email_check_token';
            $link = self::URL_LINK_PREPEND . $this->generateLink($user, $url);
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
                ));
            $this->get('mailer')->send($message);
            return new Response('Email was sent for a password reset request.');
        } else {
            return new Response("We don't have that record in our database.");
        }
    }

    public
    function checkPasswordResetEmailTokenAction($token)
    {
        try {
            $tokenLink = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TokenLink')
                ->findOneBy(array('token' => $token));
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        $timeIssued = $tokenLink->getCreated();
        $now = new \DateTime();
        $timeDiff = abs($timeIssued->getTimestamp() - $now->getTimestamp());
        $goodTime = $timeDiff < 24 * 60 * 60;

        if (!$tokenLink->getIsValid()) {
            return new Response("Link no longer valid.");
        }

        if (!$goodTime) {
            return new Response('The 24-hour period has already lapsed.');
        }

        if ($tokenLink->getIsValid() && $goodTime) {
            return $this->redirect($this->generateUrl('password_email_change', array(
                'token' => $token)));
        } else {
            return new Response("Link error.");
        }
    }

    public
    function changePasswordFromEmailAction($token)
    {
        $form = $this->createForm(new ResetPasswordType(), new PasswordChange(), array(
            'action' => $this->generateUrl('password_email_update', array('token' => $token)),
        ));

        return $this->render(
            'TpfndUserBundle:TpfndUser:edit.html.twig', array(
                'form' => $form->createView())
        );
    }

    public
    function updatePasswordFromEmailAction(Request $request, $token)
    {
        try {
            $tokenLink = $this->getDoctrine()
                ->getRepository('TpfndUserBundle:TokenLink')
                ->findOneByToken($token);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        if (!$tokenLink->getIsValid()) {
            return new Response('Link is no longer valid.');
        }

        $user = $tokenLink->getTpfndUser();

        if ($user) {
            $form = $this->createForm(new ResetPasswordType(), new PasswordChange());

            $form->handleRequest($request);
//            dump($request->get('resetPassword'));
//            dump($form->isValid());
//            exit();
//
//            if (!$form->isValid()) {
//                $this->get('session')->getFlashBag()->add('notice', 'Invalid form fields.');
//                return $this->redirect($this->generateUrl('password_email_change', array('token' => $token)));
//            }


            $fields = $request->get('resetPassword');
            $newPassword = $this->sha256HashPassword($fields['newpassword']['newpassword'], $user->getSalt());

            $em = $this->getDoctrine()->getManager();
            $tokenLink->setIsValid(false);
            $em->flush();
            return $this->proceedWithPasswordChange($newPassword, $user);
        } else {
            return new Response('No user found from the given email token.');
        }
    }

    public
    function registrationEmailConfirmationAction($token)
    {

        $tokenLink = $this->getDoctrine()
            ->getRepository('TpfndUserBundle:TokenLink')
            ->findOneBy(array('token' => $token));

        if (!$tokenLink->getIsValid()) {
            return new Response('Registration already confirmed.');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $tokenLink->setIsValid(false);
            $tokenLink->getTpfndUser()->setIsActive(true);
            $em->flush();
        } catch (\Exception $e) {
            return new Response('Error updating database.' . $e->getMessage());
        }

        $this->get('session')->getFlashBag()->add('notice', 'Registration confirmed.');

        return $this->redirect($this->generateUrl('login_route'));

    }

    public
    function proceedWithPasswordChange($newPassword, TpfndUser $user)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            $user->setPassword($newPassword);

            $em->flush();
        } catch (ORMException $e) {
            //TODO (proper error handling)
            return new Response($e);
        }

        $this->get('session')->getFlashBag()->add('notice', 'Successfully changed password.');

        return $this->redirect($this->generateUrl('logout'));

    }

    public
    function generateLink($user, $url)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            $rawLink = new TokenLink();
            $rawLink->setCreated(new \DateTime());
            $rawLink->setToken(md5(uniqid(rand(), true)));
            $rawLink->setTpfndUser($user);
            $rawLink->setUrl($url);
            $rawLink->setIsValid(true);

            $em->persist($rawLink);
            $em->flush();
        } catch (ORMException $e) {
            //TODO (proper error handling)
            return new Response($e);
        }

        return $this->generateUrl($rawLink->getUrl(), array('token' => $rawLink->getToken()));
    }

    public
    function sha256HashPassword($raw, $salt)
    {
        return hash('sha256', $salt . $raw);
    }

    public
    function isPasswordCorrect($retrieved, $raw, $salt)
    {
        return $retrieved === $this->sha256HashPassword($raw, $salt);
    }

}
