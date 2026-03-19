<?php

declare(strict_types=1);

namespace Pendu;

class GameRenderer
{
    private const HANGMAN_STAGES = [
        // 0 erreurs
        "
  +---+
  |   |
      |
      |
      |
      |
=========",
        // 1 erreur
        "
  +---+
  |   |
  O   |
      |
      |
      |
=========",
        // 2 erreurs
        "
  +---+
  |   |
  O   |
  |   |
      |
      |
=========",
        // 3 erreurs
        "
  +---+
  |   |
  O   |
 /|   |
      |
      |
=========",
        // 4 erreurs
        "
  +---+
  |   |
  O   |
 /|\\  |
      |
      |
=========",
        // 5 erreurs
        "
  +---+
  |   |
  O   |
 /|\\  |
 /    |
      |
=========",
        // 6 erreurs
        "
  +---+
  |   |
  O   |
 /|\\  |
 / \\  |
      |
=========",
        // 7 erreurs (mort)
        "
  +---+
  |   |
  O   |
 /|\\  |
 / \\  |
      |
========= GAME OVER",
    ];

    public function render(Game $game): string
    {
        $incorrectCount = count($game->getIncorrectLetters());
        $stageIndex = min($incorrectCount, count(self::HANGMAN_STAGES) - 1);

        $output = self::HANGMAN_STAGES[$stageIndex] . "\n\n";

        // Mot masqué
        $output .= "  Mot : " . strtoupper($game->getMaskedWord()) . "\n\n";

        // Tentatives restantes
        $output .= "  Tentatives restantes : " . $game->getRemainingAttempts() . " / " . $game->getMaxAttempts() . "\n";

        // Lettres incorrectes
        if (!empty($game->getIncorrectLetters())) {
            $output .= "  Lettres incorrectes  : " . implode(', ', $game->getIncorrectLetters()) . "\n";
        }

        // Lettres correctes
        $correct = array_diff($game->getGuessedLetters(), $game->getIncorrectLetters());
        if (!empty($correct)) {
            $output .= "  Lettres correctes    : " . implode(', ', $correct) . "\n";
        }

        // Message de fin
        if ($game->isWon()) {
            $output .= "\n  🎉 BRAVO ! Vous avez trouvé le mot : " . strtoupper($game->getWord()) . "\n";
        } elseif ($game->isLost()) {
            $output .= "\n  💀 PERDU ! Le mot était : " . strtoupper($game->getWord()) . "\n";
        }

        return $output;
    }

    public function renderWelcome(): string
    {
        return <<<EOT

  ╔════════════════════════════════╗
  ║      🎮  JEU DU PENDU  🎮      ║
  ╚════════════════════════════════╝
  Devinez le mot caché lettre par lettre.
  Vous avez 7 tentatives incorrectes.

EOT;
    }
}
