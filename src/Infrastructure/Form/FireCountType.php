<?php

declare(strict_types=1);

namespace App\Infrastructure\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for fire count submission.
 * Validation constraints are defined inline for simplicity.
 */
class FireCountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'fire_count.form.email.label',
                'attr' => [
                    'placeholder' => 'fire_count.form.email.placeholder',
                    'class' => 'input input-bordered w-full',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'validation.email.not_blank'),
                    new Assert\Email(message: 'validation.email.invalid'),
                ],
            ])
            ->add('adultsCount', IntegerType::class, [
                'label' => 'fire_count.form.adults_count.label',
                'data' => 0,
                'attr' => [
                    'min' => 0,
                    'class' => 'input input-bordered w-full',
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'validation.adults_count.not_null'),
                    new Assert\GreaterThanOrEqual(
                        value: 0,
                        message: 'validation.adults_count.negative'
                    ),
                ],
            ])
            ->add('childrenCount', IntegerType::class, [
                'label' => 'fire_count.form.children_count.label',
                'data' => 0,
                'attr' => [
                    'min' => 0,
                    'class' => 'input input-bordered w-full',
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'validation.children_count.not_null'),
                    new Assert\GreaterThanOrEqual(
                        value: 0,
                        message: 'validation.children_count.negative'
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'fire_count.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary w-full',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'space-y-4'],
        ]);
    }
}