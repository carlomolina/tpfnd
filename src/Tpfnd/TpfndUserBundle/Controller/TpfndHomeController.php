<?php

namespace Tpfnd\TpfndUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tpfnd\TpfndUserBundle\Entity\TpfndUser;

class TpfndHomeController extends Controller
{

    public function indexAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user instanceof TpfndUser) {
            return $this->render('TpfndUserBundle:Home:welcome.html.twig', array(
                'user' => $user,
                #'id' => $user->getId(),
            ));
        } else {
            return $this->render('TpfndUserBundle:Home:index.html.twig');
        }
    }

}
