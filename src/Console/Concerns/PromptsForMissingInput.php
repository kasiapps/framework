<?php

namespace Kasi\Console\Concerns;

use Closure;
use Kasi\Contracts\Console\PromptsForMissingInput as PromptsForMissingInputContract;
use Kasi\Support\Arr;
use Kasi\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Kasi\Prompts\text;

trait PromptsForMissingInput
{
    /**
     * Interact with the user before validating the input.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        if ($this instanceof PromptsForMissingInputContract) {
            $this->promptForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt the user for any missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        $prompted = (new Collection($this->getDefinition()->getArguments()))
            ->reject(fn (InputArgument $argument) => $argument->getName() === 'command')
            ->filter(fn (InputArgument $argument) => $argument->isRequired() && match (true) {
                $argument->isArray() => empty($input->getArgument($argument->getName())),
                default => is_null($input->getArgument($argument->getName())),
            })
            ->each(function (InputArgument $argument) use ($input) {
                $label = $this->promptForMissingArgumentsUsing()[$argument->getName()] ??
                    'What is '.lcfirst($argument->getDescription() ?: ('the '.$argument->getName())).'?';

                if ($label instanceof Closure) {
                    return $input->setArgument($argument->getName(), $argument->isArray() ? Arr::wrap($label()) : $label());
                }

                if (is_array($label)) {
                    [$label, $placeholder] = $label;
                }

                $answer = text(
                    label: $label,
                    placeholder: $placeholder ?? '',
                    validate: fn ($value) => empty($value) ? "The {$argument->getName()} is required." : null,
                );

                $input->setArgument($argument->getName(), $argument->isArray() ? [$answer] : $answer);
            })
            ->isNotEmpty();

        if ($prompted) {
            $this->afterPromptingForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        //
    }

    /**
     * Whether the input contains any options that differ from the default values.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return bool
     */
    protected function didReceiveOptions(InputInterface $input)
    {
        return (new Collection($this->getDefinition()->getOptions()))
            ->reject(fn ($option) => $input->getOption($option->getName()) === $option->getDefault())
            ->isNotEmpty();
    }
}
