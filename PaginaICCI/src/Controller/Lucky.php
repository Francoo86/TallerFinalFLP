<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    /*
     * @Route("/lucky", name="lucky")
     */
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><head><title>Test Symf</title></head><body><h1>Lucky number: '.$number.'</h1></body></html>'
        );
    }
}