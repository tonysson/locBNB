<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * Compact() permet de créer un tableau automatiquement en nommant les clés
     * CreateQuery() permet d'écrire une requete DQL sous forme d'une chaine de caractères
     * En DQL on ne s'interrésse pas aux tables mais aux entités!
     * GetResult() récupére les résultats sous forme d'objets Entité
     * GetSingleSclarResult() permet d'obtenir le résultat sous forme d'une variable scalaire simple et non un tableau
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(ObjectManager $manager, StatsService $statsService)
    {
        // $user = $manager->createQuery('SELECT COUNT(u)  FROM App\Entity\User u')->getSingleScalarResult();
        //$ads = $manager->createQuery('SELECT COUNT(a)  FROM App\Entity\Ad a')->getSingleScalarResult();
        //$bookings=$manager->createQuery('SELECT COUNT(b)  FROM App\Entity\Booking b')->getSingleScalarResult();
        // $commennts=$manager->createQuery('SELECT COUNT(c)  FROM App\Entity\Comment c')->getSingleScalarResult();
      // $users    = $statsService->getUsersCount();
      // $ads      = $statsService->getAdsCount();
      // $bookings = $statsService->getBookingsCount();
      // $comments = $statsService->getCommentsCount();

        $stats    = $statsService->getStats();
        $bestAds  = $statsService->getBestAds();
        $worstAds = $statsService->getWorstAds();
         
        return $this->render('admin/dashboard/index.html.twig', [
            'stats'    => $stats,
            'bestAds'  => $bestAds,
            'worstAds' => $worstAds
        ]);
    }
}
