<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\PaginationService;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     */
    public function index(AdRepository $repo,$page,PaginationService $pagination)
    {   

        // on peut faire requirements={"page": "\d+"} pour empecher une injection sql. la on precise que je veux des nombres... 
        //On peut le faire comme ca aussi: @route("/admin/ads/{page<\d+>?1}") ? pour dire que c'est optionnel et 1 c'est la valeur par defaut

        //1- pour utiliser findBy()permet de retrouver PLUSIEURS enregistrements, qui prend 4 arguments(critères,ordres,limite,offset(debut)) findBy([], [], 5 , 0): je lui dis trouve moi les enrégistrements sans critère sans ordre ds la limite de 5 apartir de 0


        //2-pour utiliser findOneBy() permet de trouver UN enrégistrement grace à des critères de recherche, les criteres st sous forme de tableau de Clés et de valeur $ad=$repo->findOneBy(['id'=>332])

       // $limit=10;
        //$start= $page *$limit -$limit;

        // rendre dynamique le html de la pagination: connaitre le total de mes enregistrements

        $pagination->setEntityClass(Ad::class)
                   ->setPage($page);
        
         
                   
        //$total = count($repo->findAll());
        // pour avoir le nombre de pages:alors si j'ai 25 pages ds la limite de 10 j'aurais dc 2.5 ce qui pose problème.Pour le regler on utilise la fction ceil() de php pour arrondir au dessus dc 3 ds mon exemple
        //$pages = ceil($total / $limit) ;

        return $this->render('admin/ad/index.html.twig', [
           'pagination' => $pagination
        ]);
    }



    /**
     * Permet d'afficher le formulaire d'dition
     * @Route("/admin/ads/{id}/edit" , name="admin_ads_edit") 
     * @param Ad $ad
     * @return Response
     */

    public function edit(Ad $ad, Request $request, ObjectManager $manager)
    {
        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifié !!"

            );

            return $this->redirectToRoute('admin_ads_index');
        }

        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    } 
    
    
    /**
     * Permet de supprimer une annonce
     * @Route("/admin/ads/{id}/delete" ,name="admin_ads_delete")
     * @return Response
     */
    
    
    public function delete(Ad $ad,ObjectManager $manager){

        if(count($ad->getBookings()) > 0 ){
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> car elle possède déjà des réservations !"
            );
        }else{   
          
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimé"
            );
        } 

        return $this->redirectToRoute('admin_ads_index');
         
    }

}
