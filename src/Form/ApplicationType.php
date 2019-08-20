<?php


namespace App\Form;




use Symfony\Component\Form\AbstractType;




class ApplicationType extends AbstractType{

    /**
     * permet d'avoir la configuration de base d'un champ
     *
     * @param [string] $label
     * @param [string] $placeholder
     * @param array $options
     * @return array
     */


    // array_merge pour fusionner deux options de type tableaux!!

    // icije le mets en protected pour que l'heritage soit effectif!!

    protected function getConfiguration($label, $placeholder, $options = [])
    {
        return array_merge([
            'label' => $label,
            'attr' => [
                'placeholder' => "$placeholder"
            ]
        ], $options);

    }
}