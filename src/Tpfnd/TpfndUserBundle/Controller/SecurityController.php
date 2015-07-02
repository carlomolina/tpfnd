<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{

    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        #username for this use case is the user's email, as per security settings
        $lastUsername = $authenticationUtils->getLastUsername();

//        $user = $this->getDoctrine()
//            ->getRepository('TpfndUserBundle:TpfndUser')
//            ->findOneByEmail($lastUsername);
//
//        if (!$user->getIsActive()) {
//            $this->get('session')->getFlashBag()->add("You need to confirm your registration first.
//            We sent you an email for this.");
//            return $this->redirect($this->generateUrl('tpfnd_home'));
//        }

        return $this->render(
            'security/login.html.twig',
            array(
                'last_email' => $lastUsername,
                'error' => $error,
            )
        );
    }
}
