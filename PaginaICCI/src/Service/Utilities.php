<?php

namespace App\Service;

//Generar un usuario basado en el correo de la UNAP.
class Utilities {
    public function generateUsername($email){
        $username = substr($email, 0, strpos($email, "@"));
        return $username;
    }
}