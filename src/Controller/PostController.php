<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    #[Route('post/{id}', name: 'post', requirements: ['id' => '\d+'])]
    public function show(Post $post, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime());
            $post->addComment($comment);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('post', ['id' => $post->getId()]);
        }

        return $this->renderForm('post/post.html.twig', [
            'title' => 'Article n°' . $post->getId(),
            'post' => $post,
            'commentForm' => $form
        ]);

     
    }


    #[Route('post/new', name: 'post_new')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {

        $post = new Post();


        //Création formulaire
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        //Redirection après validation du form
        if ($form->isSubmitted() && $form->isValid()) {
            if ($post->getId()) {
                $post->setUpdateAt(new \DateTime);
            } else {
                $post->setCreatedAt(new \DateTime());
            }
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('post', ['id' => $post->getId()]);
        }

        //Render form (pas de données post)
        return $this->renderForm('post/form.html.twig', [
            'title' => 'Nouvel article',
            'postForm' => $form,
            'updateForm' => false // Permet d'utiliser un seul form pour la création et l'edition de post
        ]);
    }

        #[Route('post/update/{id}', name: 'post_update')]
        public function update(Request $request, EntityManagerInterface $manager, Post $post = null): Response
        {
            if (!$post) {
                throw $this->createNotFoundException('Cet article n\'existe pas');
            }

            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                if ($post->getId()) {
                    $post->setUpdateAt(new \DateTime);
                } else {
                    $post->setCreatedAt(new \DateTime());
                }
                $manager->persist($post);
                $manager->flush();
    
                return $this->redirectToRoute('post', ['id' => $post->getId()]);
            }

        return $this->renderForm('post/form.html.twig', [
            'title' => 'Nouvel article',
            'postForm' => $form,
            'updateForm' => true
        ]);
    }


    #[Route("/post/delete/{id}", name: "post_delete", requirements: ["id" => "\d+"])]
    public function delete($id, PostRepository $repo, EntityManagerInterface $manager): Response
    {
        $post = $repo->find($id);

        $manager->remove($post);

        $manager->flush();

        return $this->redirectToRoute("home");
    }


    #[Route('/author/list/{id}', name: 'authors_posts')]
    public function postsByAuthor(Author $author): Response
    {
        return $this->render('post/list.html.twig',[
            'title' => 'Accueil',
            'posts' => $author->getPosts()
        ]
    );
    }
}
