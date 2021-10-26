<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

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
    public function home() {
        return $this->render('blog/home.html.twig');
    }
    
    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $entityManager) {
        
        if(!$article) {
            $article = new Article();
        }

        // On va cherche le formulaire ArticleType
        $form = $this->createForm(ArticleType::class, $article);      

        // On inspecte la requête du formulaire 
        $form->handleRequest($request);

        // Si le formulaire et soumit et si il est valide --> on exécute 
        if($form->isSubmitted() && $form->isValid()) {
            
            // si il ne comporte pas d'id -> nouveau article donc on lui donne une date
            if(!$article->getid()){
                $article->setCreatedAt(new \DateTimeImmutable());
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }
        
        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null

        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(ArticleRepository $repo ,$id) {
        $article = $repo->find($id);

        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }

}
