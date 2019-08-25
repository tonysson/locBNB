<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings", name="admin_booking_index")
     */
    public function index(BookingRepository $repo)
    {
        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repo->findAll()
        ]);
    }



    /**
     * Permet d'éditer une reservation
     *@Route("/admin/bookings/{id}/edit", name="admin_booking_edit")
     *@return Response
     */

    public function edit(Booking $booking,Request $request,ObjectManager $manager){

        $form = $this->createForm(AdminBookingType::class,$booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // 1-$manager->persist($booking) est optionnnel parce que ce n'est pas un nouveau booking. $manager a deja pri connaissance de l'existence du booking

            // 2- recalculer le prix de la reservation
            // on peut le faire comme $booking->setAmount($booking->getAd()->getPrice()* $booking->getDuration())
            // mais on plutot mettre le setAmount a 0 et aller reconfigurer notre fction prePersiste(fction qui est appeléé a chque fw qu'on crée une nouvelle reservation) en lui rajoutant @ORM\PreUpdate


            $booking->setAmount(0);
            $manager->persist($booking);
            $manager->flush();

            $this->addFlash(
                'success',
                "La réservation n°{$booking->getId()} a bien été modifié!!"
            );

            return $this->redirectToRoute("admin_booking_index");
        }


        return $this->render('admin/booking/edit.html.twig',[
            'form' =>$form->createView(),
            'booking' =>$booking
        ]);
    }



    /**
     * Permet de supprimer une reservation
     * @Route("/admin/bookings/{id}/delete" ,name="admin_booking_delete")
     * @return Response
     */

    public function delete(Booking $booking,ObjectManager $manager){

        $manager->remove($booking);
        $manager->flush();

        $this->addFlash(
            'success',
            "La resérvation a bien été supprimé"
        );

        return $this->redirectToRoute('admin_booking_index');
         

    }
}
