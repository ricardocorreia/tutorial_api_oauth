<?php

namespace cleverti\MySuperRestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;

class RestController extends FOSRestController
{
    /**
     * This route will define both your method, and path to call
     * Since this controller is set to have the prefix /api you will need to call it by:
     * GET /api/get/cleverti
     * @Get("/get/cleverti")
     */
    public function restGetAction(Request $request)
    {
        // Do something with your Request object
        $data = array(
            "name" => "cleverti",
            "extra" => "Is awesome!"
        );
        $view = $this->view($data, 200);
        return $this->handleView($view);
    }
}
