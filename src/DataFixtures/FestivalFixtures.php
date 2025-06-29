<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Festival;


class FestivalFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $locations = ['Cluj', 'Timișoara', 'Bucuresti', 'Iasi', 'Constanta'];
        $names = ['Untold', 'Electric Castle', 'Neversea', 'SummerWell', 'Jazz in the Park', 'Plai', 'Flight Festival', 'Afterhills', 'Rockstadt', 'Shine', 'Beach, Please!', 'Saga'];

        for ($i = 0; $i < 12; $i++) {
            $festival = new Festival();
            $festival->setName($names[$i]);
            $festival->setLocation($locations[$i % count($locations)]);
            $festival->setStartDate(new \DateTime("2025-07-" . str_pad((1 + $i), 2, '0', STR_PAD_LEFT)));
            $festival->setEndDate(new \DateTime("2025-07-" . str_pad((2 + $i), 2, '0', STR_PAD_LEFT)));
            $festival->setPrice(rand(50, 300));
            $manager->persist($festival);
        }

        $manager->flush();
    }
}
