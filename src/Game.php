<?php

declare(strict_types=1);

namespace Pendu;

class Game
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    private string $wordToGuess;
    private int $maxAttempts;
    private int $remainingAttempts;
    private array $guessedLetters = [];
    private array $incorrectLetters = [];
    private string $status;

    public function __construct(string $word, int $maxAttempts = 7)
    {
        if (!preg_match('/^[a-z]+$/', $word)) {
            throw new \InvalidArgumentException(
                "Le mot doit être en minuscules et ne contenir que des lettres."
            );
        }

        if ($maxAttempts <= 0) {
            throw new \InvalidArgumentException("Le nombre de tentatives doit être supérieur à 0.");
        }

        $this->wordToGuess = $word;
        $this->maxAttempts = $maxAttempts;
        $this->remainingAttempts = $maxAttempts;
        $this->status = self::STATUS_IN_PROGRESS;
    }

    /**
     * Propose a letter. Returns true if the letter is in the word, false otherwise.
     */
    public function guess(string $letter): bool
    {
        if ($this->isOver()) {
            throw new \LogicException("La partie est terminée.");
        }

        // Normalize to lowercase
        $letter = strtolower($letter);

        // Validate single alphabetical character
        if (!preg_match('/^[a-z]$/', $letter)) {
            throw new \InvalidArgumentException("Veuillez proposer une seule lettre de l'alphabet.");
        }

        // Already guessed
        if ($this->hasBeenGuessed($letter)) {
            return in_array($letter, str_split($this->wordToGuess));
        }

        // Record the guess
        $this->guessedLetters[] = $letter;

        $letterInWord = str_contains($this->wordToGuess, $letter);

        if (!$letterInWord) {
            $this->incorrectLetters[] = $letter;
            $this->remainingAttempts--;
        }

        // Update status
        $this->updateStatus();

        return $letterInWord;
    }

    /**
     * Returns the word display with guessed letters and underscores.
     */
    public function getMaskedWord(): string
    {
        if ($this->status === self::STATUS_LOST) {
            return $this->wordToGuess;
        }

        $result = '';
        foreach (str_split($this->wordToGuess) as $index => $letter) {
            if ($index > 0) {
                $result .= ' ';
            }
            $result .= in_array($letter, $this->guessedLetters) ? $letter : '_';
        }

        return $result;
    }

    public function hasBeenGuessed(string $letter): bool
    {
        return in_array(strtolower($letter), $this->guessedLetters);
    }

    public function getRemainingAttempts(): int
    {
        return $this->remainingAttempts;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getGuessedLetters(): array
    {
        return $this->guessedLetters;
    }

    public function getIncorrectLetters(): array
    {
        return $this->incorrectLetters;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function isOver(): bool
    {
        return $this->status !== self::STATUS_IN_PROGRESS;
    }

    public function getWord(): string
    {
        return $this->wordToGuess;
    }

    private function updateStatus(): void
    {
        if ($this->remainingAttempts <= 0) {
            $this->status = self::STATUS_LOST;
            return;
        }

        // Check if all letters have been guessed
        foreach (str_split($this->wordToGuess) as $letter) {
            if (!in_array($letter, $this->guessedLetters)) {
                return; // At least one letter still missing
            }
        }

        $this->status = self::STATUS_WON;
    }
}
