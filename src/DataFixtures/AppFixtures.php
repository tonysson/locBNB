<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this -> encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {

        $faker = Factory::create('fr-FR');

        // creeons des roles

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser ->setFirstName('Cristiano')
                   ->setLastName('Ronaldo')
                   ->setEmail('tuto@gmail.com')
                   ->setHash($this->encoder->encodePassword($adminUser,'motdepasse'))
                   ->setPicture('http://placehold.it/64x64')
                   ->setIntroduction($faker->sentence())
                   ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>')
                   ->addUserRole($adminRole);

         $manager->persist($adminUser);          

        // Nous gerons les utilisateurs
        $users = [];
        $genres = ['male', 'female'];

        for ($i = 1; $i <= 10; $i++) {

            $user = new User();
            $genre =$faker ->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker ->numberBetween(1, 99) . '.jpg';

            if($genre == 'male'){
              $picture=  $picture . 'men/' .$pictureId;
            }else{
              $picture=  $picture . 'women/' . $pictureId;
            }

            $hash = $this->encoder->encodePassword($user,'password');


            // En Ternaire ca donne:

            // $picture = $picture . ($genre == 'male' ? 'men/' : 'women/') .$pictureId;
            //$picture .= $($genre == 'male' ? 'men/' : 'women/') .$pictureId;

            $user->setFirstName($faker->firstname($genre))
                ->setLastName($faker->lastname)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);



            $manager -> persist($user);
            $users[] = $user;
            
        }

        // Nous gerons les annonces
        for($i=1; $i<=30;$i++){
            $ad = new Ad();
            $title = $faker ->sentence();
            $coverImage = $faker->ImageUrl(1000,350);
            $introduction = $faker->paragraph(2);
            $content ='<p>' . join('</p><p>', $faker ->paragraphs(5)) . '</p>';

            $user = $users[mt_rand(0,count($users) -1 )];

            $ad -> setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(40,200))
                ->setRooms(mt_rand(1,5))
                ->setAuthor($user);


            for ($j = 1; $j <= mt_rand(2,5); $j++) {
                $image = new Image();

                $image -> setUrl($faker->imageUrl())
                       ->setCaption($faker->sentence())
                       ->setAd($ad);

                $manager->persist($image);
                        

            }

            // Gestion des reservations

            for ($j = 1; $j <= mt_rand(0, 10); $j++){

                $booking = new Booking();

                $createdAt = $faker ->dateTimeBetween('-6 months');
                $startDate = $faker ->dateTimeBetween('-3 months');
               
                // Pour avoir la endDate

                $duration = mt_rand(3,10); // on cree cette variable pour avoir le nbre de jours qu'on va passer ds l'appartement
                
                // j'ai envi d'avoir $startDate+$duration qui me donnera ma endDate(); ce que me renvoi faker ds dateTimeBetween c'est un objet de type DateTime de php qui possede une methode(fonction) modify() qui me permet d'ajouter ou d'enlever des jours, des mois, ou des années. Donc $endDate= $startDate->modify("+$duration days"). Normalment c'est bon mais on a un problème. la fonction modify va egalement modifié la $startDate du coup on aura une meme $startDate et $endDate alors que moi j'ai pas envi de modifier la $startDate(), donc je crée une nouvelle dateTime qui sera modifié...on créé donc clone($startDate) qui sera modifié par la fction modify et non notre $startDate(). CE QUI FAIT AU FINAL
                
                $endDate = (clone $startDate)->modify("+$duration days");

                

                $amount = $ad->getPrice() * $duration; // Pour avoir le prix de la resevation on multiplie le prix d'une nuit de reservation par la durée de la reservation...

                $booker = $users[mt_rand(0,count($users) -1 )];

                $comment = $faker->paragraph();

                $booking->setBooker($booker)
                        ->setAd($ad)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setCreatedAt($createdAt)
                        ->setAmount($amount)
                        ->setComment($comment);

                $manager->persist($booking);

                
            }
            
                $manager ->persist($ad);
        }
         
        $manager->flush();
    }
}
