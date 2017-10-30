<?php

namespace cleverti\MySuperRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('clevertiMySuperRestBundle:Default:index.html.twig');
    }
}
