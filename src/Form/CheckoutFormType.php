<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addressLine', TextType::class, [
                'label' => 'Adresse de livraison',
                'empty_data' => '',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir votre adresse.'])],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'empty_data' => '',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir votre ville.'])],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'empty_data' => '',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir votre code postal.'])],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'],
                'data' => 'FR',
            ]);
    }
}
