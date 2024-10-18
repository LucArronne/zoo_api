<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $roleAdmin = new Role();
        $roleAdmin->setName("Administateur");
        $roleAdmin->setValue("ROLE_ADMIN");

        $manager->persist($roleAdmin);


        $roleEmployee = new Role();
        $roleEmployee->setName("Employé");
        $roleEmployee->setValue("ROLE_EMPLOYEE");

        $manager->persist($roleEmployee);


        $roleVeternaire = new Role();
        $roleVeternaire->setName("Vétérnaire");
        $roleVeternaire->setValue("ROLE_VETERNARY");

        $manager->persist($roleVeternaire);

        $userAdmin = new User();
        $userAdmin->setEmail("admin@zoo.org");
        $userAdmin->setRole($roleAdmin);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));

        $manager->persist($userAdmin);


        $userEmployee = new User();
        $userEmployee->setEmail("demo@zoo.org");
        $userEmployee->setRole($roleEmployee);
        $userEmployee->setName("Demo");
        $userEmployee->setPassword($this->userPasswordHasher->hashPassword($userEmployee, "password"));

        $manager->persist($userEmployee);



        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment;
            $comment->setPseudo("pseudo" . $i);
            $comment->setText("comment" . $i);
            $manager->persist($comment);

        }

        $manager->flush();
    }
}
