<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setPassword("password$i");
            $user->setRole("ROLE_USER");
            $user->setToken(bin2hex(random_bytes(10)));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
