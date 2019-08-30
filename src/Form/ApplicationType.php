<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType{

    /**
     * permet d'avoir la configuration de base d'un champ ds tout mon site
     *
     * @param [string] $label
     * @param [string] $placeholder
     * @param array $options
     * @return array
     */


    // array_merge_recursive pour fusionner deux options de type tableaux!!

    // ici je le mets en protected pour que l'heritage soit effectif!!

    protected function getConfiguration($label, $placeholder, $options = [])
    {
        return array_merge_recursive([
            'label' => $label,
            'attr' => [
                'placeholder' => "$placeholder"
            ]
        ], $options);

    }
}