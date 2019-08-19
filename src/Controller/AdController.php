<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{

    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        $ads = $repo->findAll();
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }


    /**
     * @Route("/ads/new",name="ads_create")
     * @return Response
     * 
     */

    public function create(Request $request , ObjectManager $manager )
    {
        $ad = new Ad();

        $form =$this -> createForm(AnnonceType::class,$ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages()as $image){
                $image->setAd($ad);
                $manager->persist($image);

            }
            //$manager = $this->getDoctrine()->getManager();
            $manager ->persist($ad);
            $manager ->flush();

            $this->addFlash(
                'success',
                "l'annonce  à bien été enrégistreé!!" 
            );

            return $this->redirectToRoute('ads_show',[
                'slug' =>$ad ->getSlug()
            ]);
        }

        return $this->render('ad/new.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * 
     * @Route("/ads/{slug}/edit",name="ads_edit")
     * @return Response
     * 
     */

    public function edit(Ad $ad,Request $request, ObjectManager $manager){

        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }
            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "les modifications  ont bien été enrégistreés !!"
            );

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig',[
            'form' => $form->createView(),
            'ad' => $ad
        ]);


    }




    /**
     * @Route("/ads/{slug}",name="ads_show")
     * @return Response
     */

    public function show($slug, AdRepository $repo)
    {
        // ici je recupere l'annonce qui correspond au slug
        $ad = $repo->findOneBySlug($slug);

        return $this->render('ad/show.html.twig', [
            'ad' => $ad

        ]);
    }


   



    

    
}
