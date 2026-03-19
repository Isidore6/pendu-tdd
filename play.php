#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Pendu\Dictionary;
use Pendu\Game;
use Pendu\GameRenderer;

$renderer = new GameRenderer();
$dictionary = new Dictionary();

echo $renderer->renderWelcome();

do {
    // Nouvelle partie
    $word = $dictionary->getRandomWord();
    $game = new Game($word);

    echo "  Un mot de " . strlen($word) . " lettres a été choisi.\n\n";

    while (!$game->isOver()) {
        echo $renderer->render($game);
        echo "\n  Votre lettre : ";

        $input = trim(fgets(STDIN) ?: '');

        if ($input === '') {
            echo "  ⚠ Saisie vide, essayez à nouveau.\n";
            continue;
        }

        $letter = strtolower($input[0]);

        if (!preg_match('/^[a-z]$/', $letter)) {
            echo "  ⚠ Caractère invalide. Entrez une lettre.\n";
            continue;
        }

        if ($game->hasBeenGuessed($letter)) {
            echo "  ⚠ Vous avez déjà proposé la lettre '$letter'.\n";
            continue;
        }

        $correct = $game->guess($letter);
        echo $correct
            ? "  ✅ La lettre '$letter' est dans le mot !\n"
            : "  ❌ La lettre '$letter' n'est pas dans le mot.\n";

        echo "\n";
    }

    // Affichage final
    echo $renderer->render($game);

    echo "\n  Rejouer ? (o/n) : ";
    $again = trim(strtolower(fgets(STDIN) ?: 'n'));

} while ($again === 'o' || $again === 'oui');

echo "\n  Merci d'avoir joué ! À bientôt 👋\n\n";
