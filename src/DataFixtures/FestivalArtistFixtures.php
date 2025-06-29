<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Festival;
use App\Entity\FestivalArtist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FestivalArtistFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $festivalRepo = $manager->getRepository(Festival::class);
        $artistRepo = $manager->getRepository(Artist::class);

        $festivals = $festivalRepo->findAll();
        $artists = $artistRepo->findAll();

        foreach ($festivals as $festival) {
            $randomArtist = $artists[array_rand($artists)];

            $fa = new FestivalArtist();
            $fa->setFestival($festival);
            $fa->setArtist($randomArtist);

            $manager->persist($fa);
        }

        $manager->flush();
    }
}
