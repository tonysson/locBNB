<?php


namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginationService{

 // Services et Dépendances: Dans un service l'injection se fait via le constructeur


     private $entityClass;
     private $limit = 10; 
     private $currentPage = 1;
     private $manager;
     private $twig;
     private $route;
     private $templatePath;
     
     // RequestStack: a utiliser quand on veut acceder a la Request depuis un service

     public function __construct(ObjectManager $manager,Environment $twig,RequestStack $request,$templatePath)
     {
         $this->manager = $manager;
         // ici c'est pour pouvoir ne plus faire include ds mes template mais les remplacer par {{pagination.display()}}
         $this->twig =$twig;
         // ici c'est pour pouvoir ne pas repeter setRoute() ds mes controlleurs
         $this->route = $request->getCurrentRequest()->attributes->get('_route');

         // ici pour utiliser mon templatepath() qu'on va configurer ds service.yaml

        $this->templatePath = $templatePath; 
     }

   // le templatepath est crée pour ne plus mettre en dure admin/partials/pagination.html.twig au niveau de display() 
     public function setTemplatePath($templatePath){
         $this->templatePath = $templatePath;

        return $this;
     }

     public function getTemplatePath(){
         return $this->templatePath;
     }


     public function setRoute($route){
         $this->route = $route;

         return $this;
     }

     public function getRoute(){
         return $this->route;
     }

     public function display(){

        $this->twig->display($this->templatePath,[
           'page' =>$this->currentPage,
           'pages' => $this->getPages(),
           'route' => $this->route


        ]);
     }




     public function getPages(){

        // cette condition est à utliser pour aider  les futurs developpeurs sur quel classe et quelle page j'utilise la pagination

        if(empty($this->entityClass)){

        throw new \Exception("Vous n'avez pas specifié l'entity sur laquelle nous devons paginer, Utilisez la methode setEntityClass() de votre objet !!");
        }

        //1) Connaitre le total des enrégistrements de la table 

        $repo= $this->manager->getRepository($this->entityClass);
        $total = count( $repo->findAll());

        //2) Faire la division ,l'arrondi et le renvoyer

        $pages =  ceil($total/$this->limit);

        //3) Renvoyer les elements

        return $pages;
     }



     public function getData(){

        if (empty($this->entityClass)) {

        throw new \Exception("Vous n'avez pas specifié l'entity sur laquelle nous devons paginer, Utilisez la methode setEntityClass() de votre objet !!");
        }

      //1) Calculer l'offset

      $offset = $this->currentPage * $this->limit - $this->limit;
      // exmple $start= $page * $limit -$limit;

      //2) Demander au ripository de trouver les éléments en question
      $repo =$this->manager->getRepository($this->entityClass);
      $data = $repo -> findBy([], [], $this->limit, $offset);

      //3) Renvoyer les éléménts en question

      return $data;



     }



    public function setPage($page)
    {
        $this->currentPage = $page;

        return $this;
    }


    public function getPage()
    {
        return $this->currentPage;
    }


    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }


    public function getLimit()
    {
        return $this->limit;
    }


     public function setEntityClass($entityClass){
         $this->entityClass = $entityClass;

         return $this;
     }


    public function getEntityClass()
    {
        return $this ->entityClass;
    }


}