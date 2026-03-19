<?php

declare(strict_types=1);

namespace Pendu\Tests;

use Pendu\Dictionary;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Construction & validation
    // -------------------------------------------------------------------------

    public function testDictionaryCanBeCreatedWithDefaultWords(): void
    {
        $dictionary = new Dictionary();
        $this->assertGreaterThan(0, $dictionary->count());
    }

    public function testDictionaryCanBeCreatedWithCustomWords(): void
    {
        $dictionary = new Dictionary(['chat', 'chien', 'oiseau']);
        $this->assertSame(3, $dictionary->count());
    }

    public function testDictionaryThrowsExceptionForWordWithUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dictionary(['Chat']);
    }

    public function testDictionaryThrowsExceptionForWordWithNumbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dictionary(['chat2']);
    }

    public function testDictionaryThrowsExceptionForWordWithSpecialChars(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dictionary(['chat!']);
    }

    public function testDictionaryThrowsExceptionForWordWithSpaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dictionary(['chat chien']);
    }

    // -------------------------------------------------------------------------
    // getWords
    // -------------------------------------------------------------------------

    public function testGetWordsReturnsAllWords(): void
    {
        $words = ['chat', 'chien', 'oiseau'];
        $dictionary = new Dictionary($words);
        $this->assertSame($words, $dictionary->getWords());
    }

    // -------------------------------------------------------------------------
    // getRandomWord
    // -------------------------------------------------------------------------

    public function testGetRandomWordReturnsAWordFromTheDictionary(): void
    {
        $words = ['chat', 'chien', 'oiseau'];
        $dictionary = new Dictionary($words);
        $word = $dictionary->getRandomWord();
        $this->assertContains($word, $words);
    }

    public function testGetRandomWordThrowsExceptionWhenDictionaryIsEmpty(): void
    {
        // We need to bypass validation via reflection to create empty dictionary
        $dictionary = new Dictionary(['a']); // valid init
        $reflection = new \ReflectionClass($dictionary);
        $prop = $reflection->getProperty('words');
        $prop->setAccessible(true);
        $prop->setValue($dictionary, []);

        $this->expectException(\RuntimeException::class);
        $dictionary->getRandomWord();
    }

    public function testGetRandomWordReturnsDifferentWordsOverMultipleCalls(): void
    {
        // With enough words, at least two different words should appear over many calls
        $words = ['chat', 'chien', 'oiseau', 'lapin', 'serpent', 'tigre', 'lion'];
        $dictionary = new Dictionary($words);

        $results = [];
        for ($i = 0; $i < 50; $i++) {
            $results[] = $dictionary->getRandomWord();
        }

        // There should be more than one distinct word in 50 draws
        $this->assertGreaterThan(1, count(array_unique($results)));
    }
}
