<?php

declare(strict_types=1);

namespace Pendu;

class Dictionary
{
    private array $words;

    public function __construct(array $words = [])
    {
        if (empty($words)) {
            $this->words = [
                'programmation',
                'developpement',
                'algorithme',
                'ordinateur',
                'clavier',
                'variable',
                'fonction',
                'tableau',
                'boucle',
                'classe',
                'objet',
                'methode',
                'heritage',
                'interface',
                'abstraction',
            ];
        } else {
            $this->words = $words;
        }

        // Validate all words
        foreach ($this->words as $word) {
            $this->validateWord($word);
        }
    }

    private function validateWord(string $word): void
    {
        if (!preg_match('/^[a-z]+$/', $word)) {
            throw new \InvalidArgumentException(
                "Le mot '{$word}' est invalide. Les mots doivent être en minuscules et ne contenir que des lettres."
            );
        }
    }

    public function getRandomWord(): string
    {
        if (empty($this->words)) {
            throw new \RuntimeException("Le dictionnaire est vide.");
        }

        return $this->words[array_rand($this->words)];
    }

    public function getWords(): array
    {
        return $this->words;
    }

    public function count(): int
    {
        return count($this->words);
    }
}
