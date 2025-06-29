<?php

namespace App\DataFixtures;

use App\Entity\Festival;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PurchaseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userRepo = $manager->getRepository(User::class);
        $festivalRepo = $manager->getRepository(Festival::class);

        $users = $userRepo->findAll();
        $festivals = $festivalRepo->findAll();

        foreach ($users as $user) {
            $randomFestival = $festivals[array_rand($festivals)];

            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setFestival($randomFestival);

            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
