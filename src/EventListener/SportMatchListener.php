<?php

namespace App\EventListener;

use App\Entity\SportMatch;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;
use App\Entity\Registration;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: SportMatch::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: SportMatch::class)]
class SportMatchListener
{
    private Security $security;
    private LoggerInterface $logger;

    // On injecte la sécurité (pour savoir qui modifie) et le Logger (pour les notifications)
    public function __construct(Security $security, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->logger = $logger;
    }

    public function preUpdate(SportMatch $match, PreUpdateEventArgs $event): void
    {
        // 1. RÈGLE : Si les deux scores sont remplis, le match est "terminé"
        if ($match->getScorePlayer1() !== null && $match->getScorePlayer2() !== null) {
            $match->setStatus('terminé');
        }

        // 2. RÈGLE : Notifications
        $user = $this->security->getUser();

        // Si l'utilisateur connecté est un Admin, on arrête là (aucune notification)
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            return;
        }

        // Si le score 1 vient d'être modifié et que le score 2 est toujours vide
        if ($event->hasChangedField('scorePlayer1') && $match->getScorePlayer2() === null) {
            // Ici, tu pourrais utiliser Symfony Mailer. On utilise le Logger pour simuler la notification.
            $this->logger->info("NOTIFICATION : Le joueur 1 a mis à jour son score. Au tour du joueur 2 !");
        }

        // Si le score 2 vient d'être modifié et que le score 1 est toujours vide
        if ($event->hasChangedField('scorePlayer2') && $match->getScorePlayer1() === null) {
            $this->logger->info("NOTIFICATION : Le joueur 2 a mis à jour son score. Au tour du joueur 1 !");
        }

        $tournament = $match->getTournament();
        if ($tournament->getWinner() !== null) {
            $winnerName = $tournament->getWinner()->getUsername();
            
            // Simulation de notification à tous les participants
            foreach ($tournament->getRegistrations() as $registration) {
                $participant = $registration->getPlayer()->getUsername();
                $this->logger->info("NOTIFICATION à $participant : Le tournoi est terminé ! Le vainqueur est $winnerName.");
            }
        }
    }
    public function prePersist(SportMatch $match): void
    {
        $tournament = $match->getTournament();
        $p1 = $match->getPlayer1();
        $p2 = $match->getPlayer2();

        // On récupère toutes les inscriptions confirmées du tournoi
        $registrations = $tournament->getRegistrations();
        $confirmedPlayers = [];

        foreach ($registrations as $reg) {
            if ($reg->getStatus() === 'confirmée') {
                $confirmedPlayers[] = $reg->getPlayer()->getId();
            }
        }

        // Vérification si le joueur 1 et le joueur 2 sont dans la liste
        if (!in_array($p1->getId(), $confirmedPlayers) || !in_array($p2->getId(), $confirmedPlayers)) {
            throw new AccessDeniedHttpException("Un des joueurs n'est pas inscrit (ou confirmé) pour ce tournoi !");
        }
    }
}