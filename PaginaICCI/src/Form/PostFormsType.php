<?php

namespace App\Form;

use App\Entity\Posts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/* TODO: Slideshow de imagenes (si es que hay).
         Sistema de comentarios (si es que alcanza el tiempo).
         Sistema de perfiles más completo (lo mismo que el de arriba si es que alcanza el tiempo).

*/

class PostFormsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'constraints' => [new NotBlank(['message' => 'Por favor introduzca un titulo al post.'])],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])

            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '15'
                ]
            ])

            ->add('category', ChoiceType::class, [
                'placeholder' => 'Seleccione una categoria',

                'choices' => [
                    'Anuncios' => 'anuncios',
                    'Servicios' => 'servicios',
                    'Eventos' => 'eventos',
                ],

                'attr' => [
                    'class' => 'form-select mt-3'
                ]
            ])

            ->add('repImage', FileType::class, [
                'mapped' => false,
                'required' => false,

                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control mb-3'
                ]
            ])

            ->add('contentImages', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control mb-3'
                ],

                'constraints' => new All([
                    'constraints' =>  new File([
                        'maxSize' => '4M',

                        //Un gif, why not?
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                            'image/gif'
                        ],
    
                        'mimeTypesMessage' => 'Por favor suba una imagen válida.',
                        'maxSizeMessage' => 'Por favor suba imagenes con un tamaño menor a 4 MB.'
                    ])
                ])
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
