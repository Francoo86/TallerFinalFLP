<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\CreateCommentType;
use App\Form\DeleteCommentType;
use App\Repository\CommentsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//TODO: Realizar un AJAX.
//Esto necesita definitivamente un AJAX.

class CommentController extends AbstractController
{
    private $commentRepo;
    private $em;

    public function __construct(CommentsRepository $commentRepo, EntityManagerInterface $em)
    {
        $this->commentRepo = $commentRepo;
        $this->em = $em; 
    }

    #[Route('/comentarios_test', name: 'comentario_test')]
    public function commentTest(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/comment/{id}/editar', methods: ['POST'], name: 'editar_comentario')]
    public function commentEdit($id, Request $req): Response {
        $currComment = $this->commentRepo->findOneBy(['id' => $id, 'deletedAt' => null]); //find($id);
        
        if(!$currComment || $currComment->getAuthor() !== $this->getUser()){
            return $this->redirectToRoute('icci_inicio');
        }

        $date = new DateTime();
        $currComment->setModifiedAt($date);
        
        //No tengo idea porque las otras veces no me dejó. Pero esta es la solución temporal.
        $content =$req->request->all()['create_comment']['content'];
        $currComment->setContent($content);
        
         
        //pasar el contenido (ya que es lo unico que se modifica)
        $this->em->flush();
    
        return $this->redirectToRoute('mostrar_post', ['id' => $currComment->getPost()->getId()]);
    }

    public function editCommentForm($id, $content){
        $form = $this->createForm(CreateCommentType::class);

        return $this->render("posts/updatecomment.html.twig", [
            'update_comment_form' => $form->createView(),
            'comment_id' => $id,
            'content' => $content,
        ]);
    }


    #[Route('/comment/{id}/borrar', name: 'borrar_comentario')]
    public function commentDelete($id): Response {
        $currComment = $this->commentRepo->findOneBy(['id' => $id, 'deletedAt' => null]); //find($id);
        
        if(!$currComment || $currComment->getAuthor() !== $this->getUser()){
            return $this->redirectToRoute('icci_inicio');
        }

        $date = new DateTime();
        $currComment->setDeletedAt($date);

        $this->em->flush();

        return $this->redirectToRoute('mostrar_post', ['id' => $currComment->getPost()->getId()]);
    }

    public function deleteForm($id) {
        $deleteForm = $this->createForm(DeleteCommentType::class);

        return $this->render("posts/delete_comment.html.twig", [
            'del_form_comment' => $deleteForm->createView(),
            'id' => $id
        ]);
    }
}
