<?php

namespace Form\Type;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Isha\AdminBundle\Form\DataTransformer\AdminToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;

class UserType extends AbstractType
{
    private $em;

    public function __construct($orm,AuthorizationChecker $context,$container,$hrEm=null)
    {
        $this->em = $orm->getManager();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName',TextType::class,array(
                'attr' => array('class' => 'user-firstname'),
            ))
            ->add('lastName',TextType::class,array(
                'attr' => array('class' => 'user-lastname'),
            ))
            ->add('phone',TextType::class,array(
                'attr' => array('class' => 'user-phone'),
            ))
            ->add('email',TextType::class,array(
                'attr' => array('class' => 'user-email'),
            ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Entity\User'
        ));
    }

    public function getName()
    {
        return 'User';
    }
}