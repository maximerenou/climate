<?php

namespace League\CLImate\TerminalObject\Dynamic;

use League\CLImate\Util\Reader\ReaderInterface;
use League\CLImate\Util\Reader\Stdin;

class Radio extends InputAbstract
{
    /**
     * The options to choose from
     *
     * @var Radio\RadioGroup $radios
     */
    protected $radios;

    public function __construct($prompt, array $options, ReaderInterface $reader = null)
    {
        $this->prompt  = $prompt;
        $this->reader  = $reader ?: new Stdin();

        $this->radios = $this->buildRadios($options);
    }

    /**
     * Do it! Prompt the user for information!
     *
     * @return string
     */
    public function prompt()
    {
        $this->output->write($this->parser->apply($this->promptFormatted()));

        $this->writeRadios();

        $this->util->system->exec('stty sane');

        return $this->radios->getValue();
    }

    /**
     * Build out the radios
     *
     * @param array $options
     *
     * @return Radio\RadioGroup
     */
    protected function buildRadios(array $options)
    {
        return new Radio\RadioGroup($options);
    }

    /**
     * Format the prompt string
     *
     * @return string
     */
    protected function promptFormatted()
    {
        return $this->prompt . ' (press <Enter> to select)';
    }

    /**
     * Output the radios and listen for any keystrokes
     */
    protected function writeRadios()
    {
        $this->updateRadioView();

        $this->util->system->exec('stty -icanon');
        $this->output->sameLine()->write($this->util->cursor->hide());

        $this->listenForInput();
    }

    /**
     * Listen for input and act on it
     */
    protected function listenForInput()
    {
        while ($char = $this->reader->char(1)) {
            if ($this->handleCharacter($char)) {
                break;
            }

            $this->moveCursorToTop();
            $this->updateRadioView();
        }
    }

    /**
     * Take the appropriate action based on the input character,
     * returns whether to stop listening or not
     *
     * @param string $char
     *
     * @return bool
     */
    protected function handleCharacter($char)
    {
        switch ($char) {
            case "\n":
                $this->output->sameLine()->write($this->util->cursor->defaultStyle());
                $this->output->sameLine()->write("\e[0m");
                return true; // Break the while loop as well

            case "\e":
                $this->handleAnsi();
                break;
        }

        return false;
    }

    /**
     * Move the cursor to the top of the option list
     */
    protected function moveCursorToTop()
    {
        $output = $this->util->cursor->up($this->radios->count() - 1);
        $output .= $this->util->cursor->startOfCurrentLine();

        $this->output->sameLine()->write($output);
    }

    /**
     * Handle any ANSI characters
     */
    protected function handleAnsi()
    {
        switch ($this->reader->char(2)) {
            // Up arrow
            case '[A':
                $this->radios->setCurrent('previous');
                break;

            // Down arrow
            case '[B':
                $this->radios->setCurrent('next');
                break;
        }
    }

    /**
     * Re-write the radios based on the current objects
     */
    protected function updateRadioView()
    {
        $this->radios->util($this->util);
        $this->radios->output($this->output);
        $this->radios->parser($this->parser);

        $this->radios->write();
    }
}
