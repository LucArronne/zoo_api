<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment;
            $comment->setPseudo("pseudo" . $i);
            $comment->setText("comment" . $i);
            $manager->persist($comment);

        }

        $manager->flush();
    }
}
