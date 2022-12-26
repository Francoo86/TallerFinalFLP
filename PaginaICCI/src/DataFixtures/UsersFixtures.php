<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passHasher;

    public function __construct(UserPasswordHasherInterface $passHasher)
    {  
        $this->passHasher = $passHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new Users();
        $user->setUsername("fcalderonm");
        $user->setPassword($this->passHasher->hashPassword($user, "testhl2"));
        $user->setEmail("fcalderonm@estudiantesunap.cl");
        $user->setRoles(["ROLE_ADMIN", "ROLE_USER"]);
        $manager->merge($user);

        $manager->flush();
    }
}
