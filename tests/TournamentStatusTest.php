<?php

namespace App\Tests;

use App\Entity\Tournament;
use PHPUnit\Framework\TestCase;

class TournamentStatusTest extends TestCase
{
    public function testGetStatusDynamic(): void
    {
        $tournament = new Tournament();

        // Cas 1 : À venir (dates dans le futur)
        $tournament->setStartDate(new \DateTime('+2 days'));
        $tournament->setEndDate(new \DateTime('+5 days'));
        $this->assertEquals('À venir', $tournament->getStatus());

        // Cas 2 : Terminé (dates dans le passé)
        $tournament->setStartDate(new \DateTime('-10 days'));
        $tournament->setEndDate(new \DateTime('-5 days'));
        $this->assertEquals('Terminé', $tournament->getStatus());

        // Cas 3 : En cours (aujourd'hui est entre les deux)
        $tournament->setStartDate(new \DateTime('-1 day'));
        $tournament->setEndDate(new \DateTime('+1 day'));
        $this->assertEquals('En cours', $tournament->getStatus());
    }
}