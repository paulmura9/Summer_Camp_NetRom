<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserDetails;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserDetailsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $userRepo = $manager->getRepository(User::class);
        $users = $userRepo->findAll();

        foreach ($users as $i => $user) {
            $details = new UserDetails();
            $details->setUser($user);
            $details->setName("UserName " . ($i + 1));
            $details->setAge(20 + ($i % 10));

            $manager->persist($details);
        }

        $manager->flush();
    }
}
