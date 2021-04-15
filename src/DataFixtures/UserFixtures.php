<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setFirstName('suvathi');
        $user->setLastName('D');
        $user->setPhone('8072355705');
        $user->setEmail('suvathick.d@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'suvathi'));
        $user->setRoles(array('ROLE_ADMIN'));
         $manager->persist($user);

        $manager->flush();
    }
}
