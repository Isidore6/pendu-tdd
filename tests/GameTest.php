<?php

declare(strict_types=1);

namespace Pendu\Tests;

use Pendu\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Construction & validation
    // -------------------------------------------------------------------------

    public function testGameCanBeCreatedWithAValidWord(): void
    {
        $game = new Game('bonjour');
        $this->assertInstanceOf(Game::class, $game);
    }

    public function testGameThrowsExceptionForWordWithUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('Bonjour');
    }

    public function testGameThrowsExceptionForWordWithNumbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('bon2jour');
    }

    public function testGameThrowsExceptionForWordWithSpecialChars(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('bon!jour');
    }

    public function testGameThrowsExceptionForWordWithSpaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('bon jour');
    }

    public function testGameThrowsExceptionForZeroMaxAttempts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('bonjour', 0);
    }

    public function testGameThrowsExceptionForNegativeMaxAttempts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Game('bonjour', -1);
    }

    // -------------------------------------------------------------------------
    // Initial state
    // -------------------------------------------------------------------------

    public function testGameStartsWithCorrectRemainingAttempts(): void
    {
        $game = new Game('bonjour', 7);
        $this->assertSame(7, $game->getRemainingAttempts());
    }

    public function testGameStartsWithDefaultSevenAttempts(): void
    {
        $game = new Game('bonjour');
        $this->assertSame(7, $game->getRemainingAttempts());
    }

    public function testGameStartsInProgressStatus(): void
    {
        $game = new Game('bonjour');
        $this->assertSame(Game::STATUS_IN_PROGRESS, $game->getStatus());
        $this->assertFalse($game->isWon());
        $this->assertFalse($game->isLost());
        $this->assertFalse($game->isOver());
    }

    public function testGameInitialMaskedWordShowsOnlyUnderscores(): void
    {
        $game = new Game('chat');
        $this->assertSame('_ _ _ _', $game->getMaskedWord());
    }

    public function testGameStartsWithNoGuessedLetters(): void
    {
        $game = new Game('bonjour');
        $this->assertEmpty($game->getGuessedLetters());
    }

    public function testGameStartsWithNoIncorrectLetters(): void
    {
        $game = new Game('bonjour');
        $this->assertEmpty($game->getIncorrectLetters());
    }

    // -------------------------------------------------------------------------
    // Guessing letters
    // -------------------------------------------------------------------------

    public function testGuessCorrectLetterReturnsTrueAndRevealsLetter(): void
    {
        $game = new Game('chat');
        $result = $game->guess('c');
        $this->assertTrue($result);
        $this->assertStringContainsString('c', $game->getMaskedWord());
    }

    public function testGuessIncorrectLetterReturnsFalse(): void
    {
        $game = new Game('chat');
        $result = $game->guess('z');
        $this->assertFalse($result);
    }

    public function testGuessCorrectLetterDoesNotDecrementAttempts(): void
    {
        $game = new Game('chat', 7);
        $game->guess('c');
        $this->assertSame(7, $game->getRemainingAttempts());
    }

    public function testGuessIncorrectLetterDecrementsAttempts(): void
    {
        $game = new Game('chat', 7);
        $game->guess('z');
        $this->assertSame(6, $game->getRemainingAttempts());
    }

    public function testGuessIsNormalizedToLowercase(): void
    {
        $game = new Game('chat');
        $result = $game->guess('C'); // uppercase input
        $this->assertTrue($result);
        $this->assertContains('c', $game->getGuessedLetters());
    }

    public function testGuessThrowsExceptionForNonAlphabeticCharacter(): void
    {
        $game = new Game('chat');
        $this->expectException(\InvalidArgumentException::class);
        $game->guess('1');
    }

    public function testGuessThrowsExceptionForMultipleCharacters(): void
    {
        $game = new Game('chat');
        $this->expectException(\InvalidArgumentException::class);
        $game->guess('ab');
    }

    public function testGuessThrowsExceptionForEmptyString(): void
    {
        $game = new Game('chat');
        $this->expectException(\InvalidArgumentException::class);
        $game->guess('');
    }

    // -------------------------------------------------------------------------
    // Already guessed letters
    // -------------------------------------------------------------------------

    public function testAlreadyGuessedLetterIsDetected(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $this->assertTrue($game->hasBeenGuessed('c'));
    }

    public function testAlreadyGuessedLetterDoesNotDecrementAttempts(): void
    {
        $game = new Game('chat', 7);
        $game->guess('z'); // incorrect: 6 remaining
        $game->guess('z'); // already guessed: should stay at 6
        $this->assertSame(6, $game->getRemainingAttempts());
    }

    public function testAlreadyGuessedLetterIsNotAddedTwice(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $game->guess('c'); // duplicate
        $this->assertSame(1, count(array_filter($game->getGuessedLetters(), fn($l) => $l === 'c')));
    }

    // -------------------------------------------------------------------------
    // Masked word display
    // -------------------------------------------------------------------------

    public function testMaskedWordRevealsCorrectlyGuessedLetters(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $game->guess('a');
        // "chat" : c=0, h=1, a=2, t=3 → 'c _ a _'
        $this->assertSame('c _ a _', $game->getMaskedWord());
    }

    public function testMaskedWordRevealsAllOccurrencesOfALetter(): void
    {
        $game = new Game('maman');
        $game->guess('m');
        // 'm' appears at positions 0 and 2
        $this->assertSame('m _ m _ _', $game->getMaskedWord());
    }

    public function testMaskedWordShowsFullWordWhenAllLettersGuessed(): void
    {
        $game = new Game('chat');
        foreach (['c', 'h', 'a', 't'] as $letter) {
            $game->guess($letter);
        }
        $this->assertSame('c h a t', $game->getMaskedWord());
    }

    // -------------------------------------------------------------------------
    // Win condition
    // -------------------------------------------------------------------------

    public function testGameIsWonWhenAllLettersAreGuessed(): void
    {
        $game = new Game('chat');
        foreach (['c', 'h', 'a', 't'] as $letter) {
            $game->guess($letter);
        }
        $this->assertTrue($game->isWon());
        $this->assertSame(Game::STATUS_WON, $game->getStatus());
    }

    public function testGameIsNotWonWhileLettersAreMissing(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $this->assertFalse($game->isWon());
    }

    // -------------------------------------------------------------------------
    // Loss condition
    // -------------------------------------------------------------------------

    public function testGameIsLostWhenAttemptsReachZero(): void
    {
        $game = new Game('chat', 3);
        $game->guess('z');
        $game->guess('x');
        $game->guess('w');
        $this->assertTrue($game->isLost());
        $this->assertSame(Game::STATUS_LOST, $game->getStatus());
    }

    public function testMaskedWordRevealsFullWordOnLoss(): void
    {
        $game = new Game('chat', 1);
        $game->guess('z'); // wrong: game over
        $this->assertSame('chat', $game->getMaskedWord());
    }

    public function testRemainingAttemptsDoesNotGoBelowZero(): void
    {
        $game = new Game('chat', 1);
        $game->guess('z'); // game over
        // Trying to play again should throw
        $this->expectException(\LogicException::class);
        $game->guess('x');
    }

    // -------------------------------------------------------------------------
    // Post-game state
    // -------------------------------------------------------------------------

    public function testCannotGuessAfterGameIsWon(): void
    {
        $game = new Game('ab');
        $game->guess('a');
        $game->guess('b'); // won
        $this->expectException(\LogicException::class);
        $game->guess('c');
    }

    public function testCannotGuessAfterGameIsLost(): void
    {
        $game = new Game('chat', 1);
        $game->guess('z'); // lost
        $this->expectException(\LogicException::class);
        $game->guess('c');
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function testGetWordReturnsTheHiddenWord(): void
    {
        $game = new Game('bonjour');
        $this->assertSame('bonjour', $game->getWord());
    }

    public function testGetMaxAttemptsReturnsConfiguredValue(): void
    {
        $game = new Game('bonjour', 5);
        $this->assertSame(5, $game->getMaxAttempts());
    }

    public function testIncorrectLettersAreTracked(): void
    {
        $game = new Game('chat');
        $game->guess('z');
        $game->guess('x');
        $this->assertContains('z', $game->getIncorrectLetters());
        $this->assertContains('x', $game->getIncorrectLetters());
    }

    public function testCorrectLettersAreNotInIncorrectList(): void
    {
        $game = new Game('chat');
        $game->guess('c'); // correct
        $this->assertNotContains('c', $game->getIncorrectLetters());
    }
}
