<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ICCIUnapController extends AbstractController
{
    #[Route('icci_unap', name: 'pag_icci_unap')]
    public function index(): Response
    {
        return $this->render('icci_unap/index.html.twig', [
            'controller_name' => 'ICCIUnapController',
        ]);
    }
}
