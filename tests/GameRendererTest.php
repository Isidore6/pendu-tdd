<?php

declare(strict_types=1);

namespace Pendu\Tests;

use Pendu\Game;
use Pendu\GameRenderer;
use PHPUnit\Framework\TestCase;

class GameRendererTest extends TestCase
{
    private GameRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new GameRenderer();
    }

    // -------------------------------------------------------------------------
    // Welcome screen
    // -------------------------------------------------------------------------

    public function testRenderWelcomeContainsTitle(): void
    {
        $output = $this->renderer->renderWelcome();
        $this->assertStringContainsString('JEU DU PENDU', $output);
    }

    // -------------------------------------------------------------------------
    // Masked word display
    // -------------------------------------------------------------------------

    public function testRenderShowsMaskedWordInUppercase(): void
    {
        $game = new Game('chat');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('_ _ _ _', $output);
    }

    public function testRenderRevealedLettersAreUppercase(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('C', $output);
    }

    // -------------------------------------------------------------------------
    // Attempts display
    // -------------------------------------------------------------------------

    public function testRenderShowsRemainingAttempts(): void
    {
        $game = new Game('chat', 7);
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('7 / 7', $output);
    }

    public function testRenderShowsDecrementedAttemptsAfterWrongGuess(): void
    {
        $game = new Game('chat', 7);
        $game->guess('z');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('6 / 7', $output);
    }

    // -------------------------------------------------------------------------
    // Incorrect / correct letters
    // -------------------------------------------------------------------------

    public function testRenderShowsIncorrectLetters(): void
    {
        $game = new Game('chat');
        $game->guess('z');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('z', $output);
    }

    public function testRenderDoesNotShowIncorrectLettersSectionWhenNoneYet(): void
    {
        $game = new Game('chat');
        $output = $this->renderer->render($game);
        $this->assertStringNotContainsString('incorrectes', $output);
    }

    public function testRenderShowsCorrectLetters(): void
    {
        $game = new Game('chat');
        $game->guess('c');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('c', $output);
    }

    // -------------------------------------------------------------------------
    // Hangman ASCII stages
    // -------------------------------------------------------------------------

    public function testRenderAtStartHasNoBodyParts(): void
    {
        $game = new Game('chat', 7);
        $output = $this->renderer->render($game);
        // At 0 errors the head 'O' should not appear
        $this->assertStringNotContainsString('O', $output);
    }

    public function testRenderAfterOneWrongGuessShowsHead(): void
    {
        $game = new Game('chat', 7);
        $game->guess('z'); // 1 error
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('O', $output);
    }

    // -------------------------------------------------------------------------
    // Win / loss messages
    // -------------------------------------------------------------------------

    public function testRenderShowsVictoryMessage(): void
    {
        $game = new Game('ab');
        $game->guess('a');
        $game->guess('b');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('BRAVO', $output);
        $this->assertStringContainsString('AB', $output);
    }

    public function testRenderShowsDefeatMessage(): void
    {
        $game = new Game('chat', 1);
        $game->guess('z');
        $output = $this->renderer->render($game);
        $this->assertStringContainsString('PERDU', $output);
        $this->assertStringContainsString('CHAT', $output);
    }

    public function testRenderOnLossRevealFullWord(): void
    {
        $game = new Game('chat', 1);
        $game->guess('z');
        $output = $this->renderer->render($game);
        // The full word should be visible (not masked)
        $this->assertStringNotContainsString('_ _ _ _', $output);
    }

    public function testRenderInProgressHasNoEndMessage(): void
    {
        $game = new Game('chat');
        $output = $this->renderer->render($game);
        $this->assertStringNotContainsString('BRAVO', $output);
        $this->assertStringNotContainsString('PERDU', $output);
    }
}
