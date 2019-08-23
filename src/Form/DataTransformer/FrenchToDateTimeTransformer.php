<?php
// Pour definir le format de date sinn on a une erreur de type 
//Excepted argument of type "Datatimeinterface" "string" given!!!!

// Les dataTransformers servent a transformer une donnéé pour que qd elle apparait ds un formulaire elle soit transformée au paravant ou au contraire quand elle arrive d'un formulaire elle soit transformee pour se conformer a ce qu'elle devrait etre. Ici ds notre cas nous avaons une date en francais qui n'a aucun sens a PHP et nous voulions qu'elle soit transformee en objet de type dateTime

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface{

// eLLE va prendre la données originelles et qui la transforme pour qu'elle s'affiche bien ds un formulaire
    // ICI elle renvoit une vraie datetime

    public function transform($date){
       if($date === null) {
           return '';
       }
        return $date->format('d/m/Y');
    }

    //elle prend la donnée qui arrive du formulaire et qui va la remetre ds le sens ou on l'attendait
    // ici recoit une date au fformat francais
    public function reverseTransform($frenchDate){
       
        // frenchDate = 23/08/2019

        if($frenchDate === null ){

            // Exception
            throw new TransformationFailedException("Vous devez fournir une date");
        }
       // je crée la dateTime qui y correspond
        $date = \DateTime::createFromFormat('d/m/Y',$frenchDate);


        if($date === false){
            //Exception
            // le message ne sera pas visible
            throw new TransformationFailedException("Le format de date n'est pas le bon!");
        }

        return $date;

    }

}