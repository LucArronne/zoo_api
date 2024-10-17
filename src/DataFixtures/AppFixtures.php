<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $avis = new Avis;
            $avis->setPseudo("pseudo" . $i);
            $avis->setComment("comment" . $i);
            $manager->persist($avis);

        }

        $manager->flush();
    }
}
