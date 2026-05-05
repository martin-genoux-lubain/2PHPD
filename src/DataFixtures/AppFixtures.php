<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Création d'un compte ADMIN
        $admin = new User();
        $admin->setEmailAddress('admin@test.com')
            ->setFirstname('Admin')
            ->setLastname('System')
            ->setUsername('admin')
            ->setStatus('actif')
            ->setRoles(['ROLE_ADMIN']);
        
        $password = $this->hasher->hashPassword($admin, 'password');
        $admin->setPassword($password);
        $manager->persist($admin);

        // 2. Création de 10 JOUEURS
        $players = [];
        for ($i = 0; $i < 10; $i++) {
            $player = new User();
            $player->setEmailAddress($faker->email)
                ->setFirstname($faker->firstName)
                ->setLastname($faker->lastName)
                ->setUsername($faker->userName)
                ->setStatus('actif');

            $password = $this->hasher->hashPassword($player, 'password');
            $player->setPassword($password);
            
            $manager->persist($player);
            $players[] = $player;
        }

        // 3. Création de 5 TOURNOIS
        for ($j = 0; $j < 5; $j++) {
            $tournament = new Tournament();
            $startDate = $faker->dateTimeBetween('now', '+1 month');
            $endDate = clone $startDate;
            $endDate->modify('+2 days');

            $tournament->setTournamentname("Tournoi de " . $faker->word)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setDescription($faker->sentence)
                ->setMaxparticipants(16)
                ->setSport($faker->randomElement(['Football', 'Tennis', 'Basket', 'E-sport']))
                ->setLocation($faker->city)
                ->setOrganizer($admin); // L'admin organise tout pour le test

            $manager->persist($tournament);
        }

        $manager->flush();
    }
}