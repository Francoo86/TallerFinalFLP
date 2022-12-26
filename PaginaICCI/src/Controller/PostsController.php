<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Posts;
use App\Form\CreateCommentType;
use App\Form\DeletePostType;
use App\Form\PostFormsType;
use App\Repository\CommentsRepository;
use App\Repository\PostsRepository;
use App\Service\Uploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostsController extends AbstractController
{
    private $fullPath = "/uploads/post_data/";
    private $postRepo;
    private $commentRepo;
    private $em;

    public function __construct(PostsRepository $postRepo, CommentsRepository $commentRepo, EntityManagerInterface $em)
    {
        $this->postRepo = $postRepo;
        $this->em = $em; 
        $this->commentRepo = $commentRepo;
    }

    /*
        Objeto->find() realiza una consulta SQL pero con WHERE.
        Objeto->findBy() realiza una consulta SQL pero con ORDER BY.
        Objeto->findOneBy(arr1, arr2) => Ejemplo: arr1 => ["id" = 5, "title" = "Amongus"], arr2 => ["id" = "DESC"]
        Esto quiere decir que vamos a realizar una consulta de esta manera SELECT * FROM MOVIES WHERE ID = 5 AND TITLE = "AMONGUS";
        Objeto->count([]) cuenta la cantidad de elementos de una tabla. Algo así como SELECT COUNT(*) FROM POSTS WHERE id
    */

    #[Route('/anuncios', name: 'anuncios')]
    public function index(): Response
    {
        $anunciosPosts = $this->postRepo->findBy(["category" => "anuncios", "deletedAt" => null], ['id' => 'DESC']);
        return $this->render('posts/index.html.twig', [
            'userposts' => $anunciosPosts,
        ]);
    }

    #[Route('/eventos', name: 'eventos')]
    public function indexEventos(): Response
    {
        $eventosPosts = $this->postRepo->findBy(["category" => "eventos", "deletedAt" => null], ['id' => 'DESC']);
        return $this->render('posts/index.html.twig', [
            'userposts' => $eventosPosts,
        ]);
    }

    #[Route('/discord', name: 'discord_sv')]
    public function indexDiscord(): Response
    {
        $eventosPosts = $this->postRepo->findBy(["category" => "eventos", "deletedAt" => null], ['id' => 'DESC']);
        return $this->render('icci_unap/discord.html.twig');
    }


    #[Route('/post/{id}', methods: ['GET', 'POST'], name: 'mostrar_post')]
    public function show($id, Request $req): Response 
    {
        $postInfo = $this->postRepo->findOneBy(["id" => $id, "deletedAt" => null]);
        $form = $this->createForm(DeletePostType::class);
        $form->handleRequest($req);
    
        if($form->isSubmitted() && $form->isValid()){
            return $this->redirectToRoute("borrar_post", ['id' => $id]);
        }

        //La unica manera de no mostrar los comentarios borrados.
        $comments = $this->commentRepo->findBy(["post" => $id, "deletedAt" => null]);

        return $this->render('posts/show.html.twig', [
            'post' => $postInfo, 
            'del_form' => $form->createView(),
            'post_comments' => $comments
        ]);
    }

    #[Route('/comment/{postId}/new', name: 'comentar_post')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addComment($postId, Request $req): Response {
        //Por si las moscas, en parte también la idea es no comentar posts borrados.
        $currPost = $this->postRepo->findOneBy(["id" => $postId, "deletedAt" => null]);
        
        if(!$currPost){
            throw new NotFoundHttpException();
        }

        $comment = new Comments();
        $addComment = $this->createForm(CreateCommentType::class, $comment);

        $addComment->handleRequest($req);

        if($addComment->isSubmitted() && $addComment->isValid()){
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setPost($currPost);
            $comment->setContent($addComment->get('content')->getData());
            $date = new DateTime();
            $comment->setCreatedAt($date);

            $this->em->persist($comment);
            $this->em->flush();

            return $this->redirectToRoute('mostrar_post', ['id' => $postId]);
        }
    }

    public function commentForm($id) : Response
    {
        $currPost = $this->postRepo->findOneBy(["id" => $id, "deletedAt" => null]);
        
        if(!$currPost){
            throw new NotFoundHttpException();
        }

        $addComment = $this->createForm(CreateCommentType::class);

        return $this->render('posts/commentbox.html.twig', [
            'comment_form' => $addComment->createView(),
            'post_id' => $id,
        ]);
    }

    #[Route('/crear_post', name: 'crear_post')]

    public function create(Request $req, Uploader $uploader): Response
    {   
        $currUser = $this->getUser();
    
        if (!$currUser){
            $this->addFlash("error", "No ha iniciado sesión.");
            return $this->redirectToRoute('icci_inicio');
        }

        $newPost = new Posts();

        $formPost = $this->createForm(PostFormsType::class, $newPost);

        $formPost->handleRequest($req);
        $currDate = date("d/m/Y");

        if ($formPost->isSubmitted() && $formPost->isValid()){
            $contentImages = $formPost->get('contentImages')->getData();
    
            $postData = $formPost->getData();
            $postData->setUser($currUser);
            $dateTime = DateTime::createFromFormat('d/m/Y', $currDate);
            $postData->setDate($dateTime);

            $repImage = $formPost->get('repImage')->getData();

            if($repImage) {
                $postPath = $uploader->upload($repImage, true);
                $postData->setRepImage($this->fullPath . $postPath);
            }
            
            //Nerfeen el atributo "multiple".
            if ($contentImages) {
                $urlArr = [];

                foreach($contentImages as $images) {
                    $tmpPath = $uploader->upload($images, true);
                    //Guardar enlaces en un arreglo.
                    $newPath = $this->fullPath . $tmpPath;

                    array_push($urlArr, $newPath);
                }

                $postData->setContentImages($urlArr);
            }

            $this->em->persist($postData);
            $this->em->flush();

            return $this->redirectToRoute('mostrar_post', ['id' => $newPost->getId()]);
        }

        return $this->render('posts/create.html.twig', [
            'form' => $formPost->createView(),
            'mode' => 'create',
        ]);
    }

    #[Route('/editar_post/{id}', name: 'editar_post')]

    public function update($id, Request $req, Uploader $uploader): Response {
        $postData = $this->postRepo->find($id);
        $currUser = $this->getUser();
    
        if (!$currUser || $postData->getUser() !== $currUser){
            $this->addFlash("error", "Acceso denegado.");
            return $this->redirectToRoute('icci_inicio');
        }

        $updateForm = $this->createForm(PostFormsType::class, $postData);

        //$catField->setMapped(false);
        $updateForm->handleRequest($req);

        //TODO: Realizar DRY.
        if($updateForm->isSubmitted() && $updateForm->isValid()){
            $contentImages = $updateForm->get('contentImages')->getData();
            //Estaría bueno colocarle un 'modificado en'.
            //$dateTime = DateTime::createFromFormat('d/m/Y', $currDate);
            //$postData->setDate($dateTime);

            $repImage = $updateForm->get('repImage')->getData();

            if($repImage) {
                $postPath = $uploader->upload($repImage, true);
                $postData->setRepImage($this->fullPath . $postPath);
            }
            
            //Nerfeen el atributo "multiple".
            if ($contentImages) {
                $urlArr = [];

                foreach($contentImages as $images) {
                    $tmpPath = $uploader->upload($images, true);
                    //Guardar enlaces en un arreglo.
                    $newPath = $this->fullPath . $tmpPath;

                    array_push($urlArr, $newPath);
                }

                $postData->setContentImages($urlArr);
            }

            $postData->setTitle($updateForm->get('title')->getData());
            $postData->setContent($updateForm->get('content')->getData());


            $this->em->flush();

            return $this->redirectToRoute('mostrar_post', ['id' => $postData->getId()]);
        }

        return $this->render('posts/create.html.twig', [
            'form' => $updateForm->createView(),
            'mode' => 'update',
            'data' => $postData,
        ]);
    }

    #[Route('borrar_post/{id}', name: 'borrar_post')]

    public function delete($id){
        //Necesitamos ser cuidadosos aquí.
        $postData = $this->postRepo->findOneBy(['id' => $id, 'deletedAt' => null]);

        if(!$postData) {
            $this->addFlash("error", "Post no encontrado.");
            return $this->redirectToRoute("icci_inicio");
        }

        $currUser = $this->getUser();

        if (!$currUser || $postData->getUser() !== $currUser){
            $this->addFlash("error", "Acceso denegado.");
            return $this->redirectToRoute('icci_inicio');
        }

        $category = $postData->getCategory();

        $currDate = new DateTime();
        $postData->setDeletedAt($currDate);
        $this->em->flush();
    
        $this->addFlash("success", "Post borrado exitosamente.");

        return $this->redirectToRoute($category);
    }

    #[Route('/servicios', name: 'servicios')]
    public function services(): Response
    {
        $servicesPosts = $this->postRepo->findBy(["category" => "servicios", "deletedAt" => null], ['id' => 'DESC']);

        return $this->render('posts/index.html.twig', [
            'userposts' => $servicesPosts,
        ]);
    }

    #[Route('/user_posts', name: 'usuario_posts')]
    public function userPosts(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('icci_inicio');
        }

        $servicesPosts = $this->postRepo->findBy(['user' => $user->getId(), "deletedAt" => null], ['id' => 'DESC']);

        return $this->render('posts/index.html.twig', [
            'userposts' => $servicesPosts,
        ]);
    }

    public function checkUser($userObj){
        $user = $this->getUser();

        if (!$user || $userObj !== $user) {
            dd($user);
            return $this->redirectToRoute('icci_inicio');
        }
    }
}
