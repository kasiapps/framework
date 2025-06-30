<?php

namespace Kasi\Prompts\Concerns;

use InvalidArgumentException;
use Kasi\Prompts\Clear;
use Kasi\Prompts\ConfirmPrompt;
use Kasi\Prompts\MultiSearchPrompt;
use Kasi\Prompts\MultiSelectPrompt;
use Kasi\Prompts\Note;
use Kasi\Prompts\PasswordPrompt;
use Kasi\Prompts\PausePrompt;
use Kasi\Prompts\Progress;
use Kasi\Prompts\SearchPrompt;
use Kasi\Prompts\SelectPrompt;
use Kasi\Prompts\Spinner;
use Kasi\Prompts\SuggestPrompt;
use Kasi\Prompts\Table;
use Kasi\Prompts\TextareaPrompt;
use Kasi\Prompts\TextPrompt;
use Kasi\Prompts\Themes\Default\ClearRenderer;
use Kasi\Prompts\Themes\Default\ConfirmPromptRenderer;
use Kasi\Prompts\Themes\Default\MultiSearchPromptRenderer;
use Kasi\Prompts\Themes\Default\MultiSelectPromptRenderer;
use Kasi\Prompts\Themes\Default\NoteRenderer;
use Kasi\Prompts\Themes\Default\PasswordPromptRenderer;
use Kasi\Prompts\Themes\Default\PausePromptRenderer;
use Kasi\Prompts\Themes\Default\ProgressRenderer;
use Kasi\Prompts\Themes\Default\SearchPromptRenderer;
use Kasi\Prompts\Themes\Default\SelectPromptRenderer;
use Kasi\Prompts\Themes\Default\SpinnerRenderer;
use Kasi\Prompts\Themes\Default\SuggestPromptRenderer;
use Kasi\Prompts\Themes\Default\TableRenderer;
use Kasi\Prompts\Themes\Default\TextareaPromptRenderer;
use Kasi\Prompts\Themes\Default\TextPromptRenderer;

trait Themes
{
    /**
     * The name of the active theme.
     */
    protected static string $theme = 'default';

    /**
     * The available themes.
     *
     * @var array<string, array<class-string<\Kasi\Prompts\Prompt>, class-string<object&callable>>>
     */
    protected static array $themes = [
        'default' => [
            TextPrompt::class => TextPromptRenderer::class,
            TextareaPrompt::class => TextareaPromptRenderer::class,
            PasswordPrompt::class => PasswordPromptRenderer::class,
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
            PausePrompt::class => PausePromptRenderer::class,
            SearchPrompt::class => SearchPromptRenderer::class,
            MultiSearchPrompt::class => MultiSearchPromptRenderer::class,
            SuggestPrompt::class => SuggestPromptRenderer::class,
            Spinner::class => SpinnerRenderer::class,
            Note::class => NoteRenderer::class,
            Table::class => TableRenderer::class,
            Progress::class => ProgressRenderer::class,
            Clear::class => ClearRenderer::class,
        ],
    ];

    /**
     * Get or set the active theme.
     *
     * @throws \InvalidArgumentException
     */
    public static function theme(?string $name = null): string
    {
        if ($name === null) {
            return static::$theme;
        }

        if (! isset(static::$themes[$name])) {
            throw new InvalidArgumentException("Prompt theme [{$name}] not found.");
        }

        return static::$theme = $name;
    }

    /**
     * Add a new theme.
     *
     * @param  array<class-string<\Kasi\Prompts\Prompt>, class-string<object&callable>>  $renderers
     */
    public static function addTheme(string $name, array $renderers): void
    {
        if ($name === 'default') {
            throw new InvalidArgumentException('The default theme cannot be overridden.');
        }

        static::$themes[$name] = $renderers;
    }

    /**
     * Get the renderer for the current prompt.
     */
    protected function getRenderer(): callable
    {
        $class = get_class($this);

        return new (static::$themes[static::$theme][$class] ?? static::$themes['default'][$class])($this);
    }

    /**
     * Render the prompt using the active theme.
     */
    protected function renderTheme(): string
    {
        $renderer = $this->getRenderer();

        return $renderer($this);
    }
}
