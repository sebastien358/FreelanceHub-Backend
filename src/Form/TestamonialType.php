<?php

namespace App\Form;

use App\Entity\Picture;
use App\Entity\Testamonial;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestamonialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('job', TextType::class)
            ->add('message', TextareaType::class)
            ->add('picture', EntityType::class, [
                'class' => Picture::class,
                'required' => false,
                'mapped' => false,
                'multiple' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Testamonial::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
