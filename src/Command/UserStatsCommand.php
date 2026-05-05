<?php

namespace App\Command;

use App\Repository\SportMatchRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:stats',
    description: 'Affiche les victoires et défaites d\'un joueur',
)]
class UserStatsCommand extends Command
{
    private SportMatchRepository $matchRepository;
    private UserRepository $userRepository;

    // On injecte les Repositories pour interroger la base de données
    public function __construct(SportMatchRepository $matchRepository, UserRepository $userRepository)
    {
        parent::__construct();
        $this->matchRepository = $matchRepository;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'L\'ID de l\'utilisateur')
            ->addArgument('tournamentId', InputArgument::OPTIONAL, 'L\'ID du tournoi (optionnel)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $tournamentId = $input->getArgument('tournamentId');

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $output->writeln('<error>Utilisateur introuvable.</error>');
            return Command::FAILURE;
        }

        // On récupère absolument tous les matchs
        $matches = $this->matchRepository->findAll();

        $victoires = 0;
        $defaites = 0;

        foreach ($matches as $match) {
            // Si un ID de tournoi est fourni, on ignore les matchs des autres tournois
            if ($tournamentId && $match->getTournament()->getId() != $tournamentId) {
                continue;
            }

            // On ne compte que les matchs terminés
            if ($match->getStatus() !== 'terminé') {
                continue;
            }

            $score1 = $match->getScorePlayer1();
            $score2 = $match->getScorePlayer2();

            // Si l'utilisateur est le joueur 1
            if ($match->getPlayer1()->getId() == $userId) {
                if ($score1 > $score2) $victoires++;
                elseif ($score1 < $score2) $defaites++;
            }
            
            // Si l'utilisateur est le joueur 2
            if ($match->getPlayer2()->getId() == $userId) {
                if ($score2 > $score1) $victoires++;
                elseif ($score2 < $score1) $defaites++;
            }
        }

        // Affichage du résultat dans le terminal
        $output->writeln("===============");
        $output->writeln("Statistiques pour " . $user->getUsername());
        if ($tournamentId) {
            $output->writeln("Tournoi ID : " . $tournamentId);
        }
        $output->writeln("===============");
        $output->writeln("<info>Victoires : $victoires</info>");
        $output->writeln("<error>Défaites : $defaites</error>");

        return Command::SUCCESS;
    }
}