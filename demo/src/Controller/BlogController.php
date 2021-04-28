<?php

//http://127.0.0.1:8000/

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\ArticleType;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\User\User as UserUser;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }
    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('blog/home.html.twig', [
            'title' => "Bienvenue prout",
            'age' => 16
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager){

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$article){
            $article = new Article();
        }

        /*
        $form = $this->createFormBuilder($article)
                     ->add('title', TextType::class)
                     ->add('content', TextareaType::class, [
                         'attr' => [
                             'placeholder' => "Contenu de l'article",
                             'class' => 'form-control'
                         ]
                     ])
                     ->add('image', TextType::class, [
                         'attr' => [
                             'placeholder' => "Image de l'article",
                             'class' => 'form-control'
                         ]
                     ])
                     ->getForm();
        */

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if(!$article->getId()){
            $article->setCreatedAt(new \DateTime());
        }

        if($form->isSubmitted() && $form->isValid()){
            $article->setCreatedAt(new \DateTime());

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/create.html.twig',[
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager){
        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreateAt(new \DateTime())
                    ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show',['id'=> $article->getId()]);
        }

        return $this->render('blog/show.html.twig', [
            'article'=> $article,
            'commentForm' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/list", name="user_list")
     */
    public function list(UserRepository $repo): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $repo->findAll();
        return $this->render('blog/list.html.twig',[
            'users' => $users
        ]);
    }

}
