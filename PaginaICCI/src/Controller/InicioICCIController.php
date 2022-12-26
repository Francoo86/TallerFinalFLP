<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InicioICCIController extends AbstractController
{
    #[Route('inicio_icci', name: 'icci_inicio')]
    public function index(): Response
    {
        return $this->render('icci_unap/index.html.twig', [
            'controller_name' => 'InicioICCIController',
        ]);
    }
}
