<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\ApplicationType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BookingType extends ApplicationType
{
     private $transformer;


    public function __construct(FrenchToDateTimeTransformer $transformer){

        $this->transformer = $transformer;

   }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate',TextType::class,$this->getConfiguration("Date d'arrivée","Votre date d'arrivée"))
            ->add('endDate', TextType::class, $this->getConfiguration("Date de départ", "Votre date de départ"))
            ->add('comment', TextareaType::class, $this->getConfiguration(false, "N'hesitez-pas à laisser un commentaire !!",["required" => false]));
         

         $builder ->get('startDate')->addModelTransformer($this->transformer);
         $builder ->get('endDate')->addModelTransformer($this->transformer);   
                      
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}

//["widget" => "single_text"] mis en option dans un champ dateType permet d'avoir un beau calendrier lol!!
