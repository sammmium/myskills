<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\ExecutorRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Select;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('users', EntityType::class, array(
//            'class' => User::class,
//            'label' => false,
//            'query_builder' => function (ExecutorRepository $er) {
//                return $er->getExecutors();
////                return $er->createQueryBuilder('u')
////                    ->select(['concat(u.username', ':',  'u.email)'])
////                    ->orderBy('u.username', 'ASC');
//            },
//            'choice_label' => 'username',
//        ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
