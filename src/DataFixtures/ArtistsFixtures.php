<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArtistsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $genres = ['Pop', 'Rock', 'Jazz', 'Electronic', 'Hip-Hop', 'Indie', 'Classical', 'Reggae', 'Folk', 'Blues', 'Metal', 'Soul'];

        for ($i = 1; $i <= 12; $i++) {
            $artist = new Artist();
            $artist->setName("Artist $i");
            $artist->setMusicalGenre($genres[array_rand($genres)]);
            $manager->persist($artist);
        }

        $manager->flush();
    }
}
