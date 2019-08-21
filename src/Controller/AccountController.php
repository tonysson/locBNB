<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * @Route("/login", name="account_login")
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils ->getLastAuthenticationError();
        $username =$utils-> getLastUsername();


        return $this->render('account/login.html.twig',[
            'hasError' => $error !== null,
            'username' => $username
        ]);

    }

    /**
     * @Route("/register",name="account_register")
     * @return Response
     */

    public function register(Request $request, ObjectManager $manager,UserPasswordEncoderInterface $encoder){

        $user = new User();
        
        $form = $this->createForm(RegistrationType::class,$user);

        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // Ici je cripte le mot de passe en bdd
            $hash = $encoder->encodePassword($user,$user->getHash());
            $user-> setHash($hash);

            $manager ->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Félicitation vous êtes inscrits, Veuillez-vous connecter !!'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig',[

            'form' => $form ->createView()

        ]);

    }


    /**
     * @Route("/account/profile", name="account_profile")
     *@IsGranted("ROLE_USER")
     * @return Response
     */

    public function profile(Request $request, ObjectManager $manager){
    // je recupère l'utlisateur en cours de connexion
        $user = $this -> getUser();

        $form = $this->createForm(AccountType::class,$user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

    
            $manager->flush();

            $this->addFlash(
                'success',
                'Félicitation vos données ont été modifiées avec succès !!'
            );
        }
        return $this->render('account/profile.html.twig',[
            'form' => $form ->createView()
        ]);

    }


    /**
     * @Route("/account/password-update",name="account_password")
     * @IsGranted("ROLE_USER")
     * @return Response
     */

    public function updatePassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){
        
        $passwordUpdate = new PasswordUpdate();

        // Pour recuperer l'utilisateur connecté actuellement;

        $user =$this->getUser();

        $form = $this -> createForm(PasswordUpdateType::class,$passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //1. verifier que le oldpassword du formulaire est le mm que le password de l'utilistaeur
             
            //$passwordUpdate->getOldPassword()== je recupère le mot de passe saisi(le vieu mot de passe)
            // et je le compare avec le mot de passe que l'utlisateur a ds la base de données avec $user->getHash

            if(!password_verify($passwordUpdate->getOldPassword(),$user->getHash())){
                
                // Gestion de l'erreur

                // Cette expression $form ->get('oldPassword') me donne l'accés au champ oldpassword qui est contenu dans le formulaire..

                $form ->get('oldPassword')->addError(new FormError("Le mot de passe saisi n'est pas votre mot de passe actuel !"));

            }else{
                // Je rentre dans le else si tout est bon!!!
                // je recupère dans la variable $newpassword le nouveau mot de passe saisi par le getNewPassword()
                //qui est contenu ds mon entité $passwordUpadte

                $newPassword = $passwordUpdate ->getNewPassword();

                // je vais donc encoder le nouveau mot de passe saisi

                $hash = $encoder->encodePassword($user,$newPassword);

                // j'enregistre donc en bdd le nouveau mot de passe haché de l'utilisateur par :

                $user->setHash($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Félicitation votre mot de passee a été modifié  avec succès !!'
                );


                return $this -> redirectToRoute('homepage');
            }

            
        }
        return $this->render('account/password.html.twig',[
            'form'=>$form->createView()
        ]);

    }



    /**
     * @Route("/account",name="account_index")
     * @IsGranted("ROLE_USER")
     * @return Response
     */

    public function myAccount(){

        return $this ->render('user/index.html.twig',[
            'user' => $this ->getUser()

        ]);

    }


    /**
     * @Route("/logout",name="account_logout")
     * 
     */

    public function logout(){

    }
}
