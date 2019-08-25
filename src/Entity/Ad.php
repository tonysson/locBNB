<?php

namespace App\Entity;

use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 * fields={"title"},
 * message="Une autre annonce possède déjà ce titre!!"
 * )
 */
class Ad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Assert\Length(min=10,max=255,minMessage="Le titre doit faire minimun 10 caractères!",
     * maxMessage="Le titre doit faire minimun 10 caractères!")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=20,minMessage="L'introduction doit faire minimun 20 caractères!")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     *  @Assert\Length(min=50,minMessage="La description détaillée doit faire minimun 50 caractères!")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url()
     */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="ad", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="ad")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="ad", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */

    public function initializeSlug()
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Permet de recuperer un auteur par rapport a une annonce
     * @param User $author
     * @return Comment | null
     */

    public function getCommentFromAuthor(User $author){
     // On veut rechercher:Est-ce que parmi ts les commentaires qui sont liés à l'annonce j'ai un commentaire dt l'auteur est le mm que celui passé a ma fonction
     foreach($this->comments as $comment){
       // si l'auteur du commentaire est egale a mon auteur ds la fction, je retourn le commentaire
        if($comment->getAuthor() === $author) return $comment;
     }
        return null;
    }

    /**
     * Permet d'obtenir la moyenne globale des notes pour cette annonce
     *
     * @return float
     */
    public function getAvgRatings(){
     // 1) calculer la somme des notations
     // La fction array_reduce() reduit le tableau des commentaires en une seule valeur
     // $this->comments c'est un array collection qd on lui pass la fction toArray() il le reduit a 1 veritale tableau; apres je dois lui preciser comment je veux reduire ce tableau en lui passant une fction() qui recoit une variable $total et $comment qui est chaque commentaire recu
     // En gros je boucle sur le tableau des commentaires que je fournis ds $this->comments et on appele la function a  chaque fois en lui passant un total qui commence a 0 et le commentaire en lui mm:return $total + $comment->getRating(); elle retourne dc le total + la note qui est sur le commentaire: $comment->getRating()
     $sum = array_reduce($this->comments->toArray(),function($total,$comment){
         return $total + $comment->getRating();
     },0);
      
     // 2) calculer la moyenne
     //$moyenne = $sum / count($this->comments): ca c'est la moyenne au cas ou le commentaire existe sinn je suis entrain de diviser un nombre par 0 au cas ou je n'ai pas de commentaire. Pour eviter ca on rajoute: on s'assure que le nombre de commentaire est superieur a 0 count($this->comments)>0
     if(count($this->comments) > 0) return $sum / count($this->comments);

     return 0;
    }
   


    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponible pour cette annonce
     * @return array un tableau d'objets dateTime representant les jours d'occupations
     * 
     */

    public function getNotAvailableDays(){
        $notAvailableDays = [];

        foreach($this->bookings as $booking){

            //La je vais calculer les jours qui se trouvent entre la date d'arrivée et de depart
            //$resultat= range(10,20,2): la fction range() de php permet de determiner toutes les etapes qui permettent d'aller
             //   de 10 à 20 en sautant de 2
             //  $resulatt = [10,12,14,16,18,20] 
             // 10 ds mon cas = $booking->getStartDate()->getTimestamp()
              //  20=$booking->getEndtDate()->getTimestamp()
              //  2= je calcul 24h sous la forme de seconde
             //   2=24h*60min*60seconde
             $resultat = range(
                 $booking->getStartDate()->getTimestamp(),
                 $booking->getEndDate()->getTimestamp(),
                 24*60*60); // est un tableau mais moi je veux avoir des jours

            // array_map est une fonction qui permet de transformer mon tableau de range() en un autre tableau qui sera le miroir de range avec des elements transformés.
            //array_map il faut lui preciser une fonction de transformation

            $days = array_map(function($dayTimestamp){
                return new \dateTime(date('Y-m-d', $dayTimestamp));
            },$resultat);

            //Dans $days j'ai donc la mm chose que $resultat sauf qu'il est en seconde, mais la je l'ai sous forme
            //de l'ensemble des jours sous la forme de dateTime qui sont entre le jour de depart
            // et le jour d'arrivee d'une reservation 

            $notAvailableDays = array_merge($notAvailableDays, $days);
        }

        return $notAvailableDays;

    }

    









    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }
}
