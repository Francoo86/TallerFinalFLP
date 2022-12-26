<?php

namespace App\Controller;

use App\Form\UserEditProfileType;
use App\Repository\UsersRepository;
use App\Service\Uploader;
use App\Service\Utilities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    private $userRepo;
    private $em;

    public function __construct(UsersRepository $userRepo, EntityManagerInterface $em)
    {
        $this->userRepo = $userRepo;
        $this->em = $em;   
    }

    #[Route('/edit_profile', name: 'profile_management')]
    public function index(Request $req, Uploader $uploader, Utilities $util): Response
    {
        $user = $this->getUser();

        if ($user === null){
            return $this->redirectToRoute('icci_inicio');
        }

        $form = $this->createForm(UserEditProfileType::class, $user);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()){
            $profilePic = $form->get('pfpUrlEdit')->getData();

            //Needs refactor.
            if($profilePic) {
                $pathName = $uploader->upload($profilePic);
                $user->setPfpUrl("/uploads/pfp_img/" . $pathName);
                $this->em->flush();
                $this->addFlash("success", "Foto de perfil cambiada.");

                return $this->redirectToRoute('profile_management');
            }

            $email = $form->get('email')->getData();

            if ($email !== "" && $user->getEmail() !== $email){
                $check = $this->userRepo->findOneBy(['email' => $email]);
                
                //Evitar que aparezca ese error de duplicados.
                if($check) {
                    $this->addFlash("error", "ERROR: Ese usuario ya existe en el sistema.");
                } else {
                    $newUsername = $util->generateUsername($email);
                    $user->setEmail($email);
                    $user->setUsername($newUsername);

                    $this->em->flush();

                    //Añadir un flash que diga success
                    $this->addFlash("success", "Cambios realizados exitosamente.");
                }

                return $this->redirectToRoute('profile_management');
            }

            $this->em->refresh($user);
        }
        //dd($form->getData());

        return $this->render('users/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
