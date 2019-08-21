<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 * fields ={"email"},
 * message="Cette adresse mail est dejà utilisee,Veuillez choisir une autre")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner votre Prenom !")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner votre Nom !")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Veuillez renseigner un email valide !")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(message="Veuillez renseigner une Url valide !")
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hash;


    /**
     * @Assert\EqualTo(propertyPath="hash", message="Les deux mots de passe ne sont pas identiques !")
     */
    public $passwordConfirm;


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10,minMessage="Votre introduction doit contenir au minimum 10 caratères !")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=50,minMessage="Votre introduction doit contenir au minimum 50 caratères !")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ad", mappedBy="author")
     */
    private $ads;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", mappedBy="users")
     */
    private $userRoles;


    public function getFullName(){

        return "{$this->firstName} {$this->lastName}";

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
            $this->slug = $slugify->slugify($this->firstName . ' ' . $this->lastName);
        }
    }

    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstName;
    }

    public function setFirstname(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|Ad[]
     */
    public function getAds(): Collection
    {
        return $this->ads;
    }

    public function addAd(Ad $ad): self
    {
        if (!$this->ads->contains($ad)) {
            $this->ads[] = $ad;
            $ad->setAuthor($this);
        }

        return $this;
    }

    public function removeAd(Ad $ad): self
    {
        if ($this->ads->contains($ad)) {
            $this->ads->removeElement($ad);
            // set the owning side to null (unless already changed)
            if ($ad->getAuthor() === $this) {
                $ad->setAuthor(null);
            }
        }

        return $this;
    }

    public function getRoles(){

        // EXPLICATIONS:

        // fction toArray() transforme une arraycoolection en un tableau classique Php
        // fction map() va boucler sur chaque role qui se trouve ds l'entite role de la table des user et les transforme en quelque chose d'autres...ds notre cas le titre du role et donc getTitle()->Role_admin

        //$roles= $this->userRoles->toArray();

      // dump($roles); pour voir le contenu du premier $roles qui est un arraycollection avec des roles

        $roles = $this->userRoles->map(function($role){
            return $role ->getTitle();
        })->toArray();

        // Tous nos utilisateurs ont le role_user (c'est sur!) par contre certains parmi eux auront le role_user et le role_admin: comment faire? on ajoute juste : $roles[] = 'Role_User'

        $roles[] = 'ROLE_USER';

        return $roles;

       // dump($roles); et un deuxieme tableau qui contien juste une chaine de caractère "role_admin"!!
      // die();
        
        
    }

    public function getPassword()
    {
        return $this->hash;
    }

    public function getSalt()
    {
        
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        
    }

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            $userRole->removeUser($this);
        }

        return $this;
    }
}
