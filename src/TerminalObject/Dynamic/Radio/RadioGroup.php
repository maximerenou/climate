<?php

namespace League\CLImate\TerminalObject\Dynamic\Radio;

use League\CLImate\Decorator\Parser\ParserImporter;
use League\CLImate\Util\OutputImporter;
use League\CLImate\Util\UtilImporter;

class RadioGroup
{
    use OutputImporter, ParserImporter, UtilImporter;

    protected $radios = [];

    protected $count;

    public function __construct(array $options)
    {
        foreach ($options as $key => $option) {
            $this->radios[] = new Radio($option, $key);
        }

        $this->count = count($this->radios);

        $this->radios[0]->setFirst()->setCurrent();
        $this->radios[$this->count - 1]->setLast();
    }

    public function write()
    {
        array_map([$this, 'writeRadio'], $this->radios);
    }

    /**
     * Retrieve the checked option values
     *
     * @return string
     */
    public function getValue()
    {
        $current = $this->getCurrent();

        if (!is_null($current)) {
            return $current[0]->getValue();
        }

        return null;
    }

    /**
     * Set the newly selected option based on the direction
     *
     * @param string $direction 'previous' or 'next'
     */
    public function setCurrent($direction)
    {
        list($option, $key) = $this->getCurrent();

        $option->setCurrent(false);

        $new_key = $this->getCurrentKey($direction, $option, $key);

        $this->radios[$new_key]->setCurrent();
    }

    /**
     * Get the number of radios
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Get the currently selected option
     *
     * @return array
     */
    protected function getCurrent()
    {
        foreach ($this->radios as $key => $option) {
            if ($option->isCurrent()) {
                return [$option, $key];
            }
        }
    }

    /**
     * Retrieve the correct current key
     *
     * @param string $direction 'previous' or 'next'
     * @param Radio $option
     * @param int $key
     *
     * @return int
     */
    protected function getCurrentKey($direction, $option, $key)
    {
        $method = 'get' . ucwords($direction). 'Key';

        return $this->{$method}($option, $key);
    }

    /**
     * @param Radio $option
     * @param int $key
     *
     * @return int
     */
    protected function getPreviousKey($option, $key)
    {
        if ($option->isFirst()) {
            return count($this->radios) - 1;
        }

        return --$key;
    }

    /**
     * @param Radio $option
     * @param int $key
     *
     * @return int
     */
    protected function getNextKey($option, $key)
    {
        if ($option->isLast()) {
            return 0;
        }

        return ++$key;
    }

    /**
     * @param Radio $checkbox
     */
    protected function writeRadio($checkbox)
    {
        $checkbox->util($this->util);
        $checkbox->parser($this->parser);

        $parsed = $this->parser->apply((string) $checkbox);

        if ($checkbox->isLast()) {
            $this->output->sameLine()->write($parsed);
            return;
        }

        $this->output->write($parsed);
    }
}
