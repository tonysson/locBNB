<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\GreaterThan;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * Permet de dire a doctrine que cette entité doit gerer son cycle 
 * c'est a dire qu'a different instant de son cycle de vie on a relié des fctions
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ad", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="La date d'arrivée doit etre au bon format !")
     * @Assert\GreaterThan("today", message="la date d'arrivée doit etre ulterieur à la date d'aujourd'hui !")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="La date de départ doit etre au bon format !")
     * @Assert\GreaterThan(propertyPath="startDate",message="La date d'arrivée doit etre anterieure à la date de depart!!")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * 
     * Callback appelé achaque fois qu'on crée une reservation
     * @ORM\Prepersist
     */

     // Si ma date de creation est vide; je creé une nouvelle dateTime a l'instant ou je le fais.
    public function prePersist(){

        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }
      // Pour avoir le prix automatique de ma reservation:prix de l'annonce* nombre de jour(du sejour)

        if(empty($this->amount)){
            // pour faire ca il faut que je calcul le nombre de jours que de la reservation
            // je cree une fction getduration() pour avoir le nombre de jours exacte

            //$this->ad->getPrice() me donne le prix de l'annonce
            //$this->getDuration() me donne le nbre de jours 

           $this->amount = $this ->ad->getPrice() * $this->getDuration();


        }
    }
    public function getDuration(){

        // la je fais tt betement $diff= endDate() - startDate() pour avoir un objet de type dateInterval
        $diff = $this->endDate->diff($this->startDate);
        // là je recupere le nombre de jour ds mon objet dateInterval
        return $diff->days;
    }

    public function isBookableDates(){

        //1- il faut connaitre les dates qui sont impossibles a l'annonce

        $notAvailableDays = $this->ad->getNotAvailableDays();

        //2- il faut comparer les dates choisies avec les dates impossibles 

        $bookingDays = $this->getDays();// la je recupere les jours de ma reservation

        // je vais donc transformer ces objets datetime en un tableau qui contiendront des string facilement comparable 

        $formatDay = function ($day) {
                return $day->format('Y-m-d');
        };
         

        $days =array_map($formatDay,$bookingDays);


        $notAvailable = array_map($formatDay, $notAvailableDays);

        foreach($days as $day){
            // array_search est une fction qui me permet de chercher une info o sein d'un tableau qui prends comme premier parametre c'est l'information
            if(array_search($day,$notAvailable) !== false) return false;
        }
        return true;

    }

    /**
     * Permet de recuperer un tableau des journee qui correspondent à ma reservation
     * @return array un tableau d'objet dateTime representant les jours de la reservation
     */


    public function getDays(){

        $resultat = range(
            $this->getStartDate()->getTimestamp(),
            $this->getEndDate()->getTimestamp(),
            24 * 60 * 60
        );

        $days = array_map(function ($dayTimestamp) {
            return new \dateTime(date('Y-m-d', $dayTimestamp));
        }, $resultat);


        return $days;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
