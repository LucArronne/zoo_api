<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\AnimalImage;
use App\Entity\Comment;
use App\Entity\Habitat;
use App\Entity\HabitatImage;
use App\Entity\Race;
use App\Entity\Role;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
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
        // Generate users and roles

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
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "admin"));

        $manager->persist($userAdmin);


        $userEmployee = new User();
        $userEmployee->setEmail("demo1@zoo.org");
        $userEmployee->setRole($roleEmployee);
        $userEmployee->setName("Demo");
        $userEmployee->setPassword($this->userPasswordHasher->hashPassword($userEmployee, "demo1"));

        $manager->persist($userEmployee);
        
        $userVeternary = new User();
        $userVeternary->setEmail("demo2@zoo.org");
        $userVeternary->setRole($roleVeternaire);
        $userVeternary->setName("Demo");
        $userVeternary->setPassword($this->userPasswordHasher->hashPassword($userEmployee, "demo2"));

        $manager->persist($userVeternary);

/*
        // Generate comments

        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment;
            $comment->setPseudo("pseudo" . $i);
            $comment->setText("comment" . $i);
            $manager->persist($comment);
        }

        // Generate sevices

        $faker  = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $service = new Service();
            $service->setName($faker->name);
            $service->setDescription($faker->realText(30));
            $service->setImage($faker->name(10));
            $manager->persist($service);
        }

        // Generate habitats

        $habitats = [];

        for ($i = 0; $i < 5; $i++) {
            $habitat = new Habitat();
            $habitat->setName($faker->name(10));
            $habitat->setDescription($faker->realText(30));
            $habitatImage = new HabitatImage();
            $habitatImage->setPath($faker->name(10));
            $habitat->addImage($habitatImage);
            $manager->persist($habitat);

            $manager->flush();
            $habitats[] = $habitat;
        }

        // Generate animals and races

        $races = [];

        for ($i = 0; $i < 5; $i++) {
            $race = new Race();
            $race->setName($faker->name);
            $manager->persist($race);
            $races[] = $race;
        }

        for ($i = 0; $i < 10; $i++) {
            $animal = new Animal();
            $animal->setName($faker->name);
            $animal->setRace($races[array_rand($races)]);
            $animalImage = new AnimalImage();
            $animalImage->setPath($faker->name(10));
            $animal->addImage($animalImage);
            $animalImage = new AnimalImage();
            $animalImage->setPath($faker->name(10));
            $animal->addImage($animalImage);
            $animal->setHabitat($habitats[array_rand($habitats)]);
            $manager->persist($animal);
            $manager->flush();
        }
*/

        $manager->flush();
    }
}
