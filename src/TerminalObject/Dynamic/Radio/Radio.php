<?php

namespace League\CLImate\TerminalObject\Dynamic\Radio;

use League\CLImate\Decorator\Parser\ParserImporter;
use League\CLImate\TerminalObject\Helper\StringLength;
use League\CLImate\Util\UtilImporter;

class Radio
{
    use StringLength, ParserImporter, UtilImporter;

    /**
     * The value of the radio
     *
     * @var string|int|bool $value
     */
    protected $value;

    /**
     * The label for the radio
     *
     * @var string|int $label
     */
    protected $label;

    /**
     * Whether pointer is currently pointing at the radio
     *
     * @var bool $current
     */
    protected $current = false;

    /**
     * Whether the radio is the first in the group
     *
     * @var bool $first
     */
    protected $first = false;

    /**
     * Whether the radio is the last in the group
     *
     * @var bool $last
     */
    protected $last = false;

    public function __construct($label, $value)
    {
        $this->value = (!is_int($value)) ? $value : $label;
        $this->label = $label;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @return bool
     */
    public function isFirst()
    {
        return $this->first;
    }

    /**
     * @return bool
     */
    public function isLast()
    {
        return $this->last;
    }

    /**
     * Set whether the pointer is currently pointing at this radio
     *
     * @param bool $current
     *
     * @return Radio
     */
    public function setCurrent($current = true)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return Radio
     */
    public function setFirst()
    {
        $this->first = true;

        return $this;
    }

    /**
     * @return Radio
     */
    public function setLast()
    {
        $this->last = true;

        return $this;
    }

    /**
     * @return string|int|bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Build out basic radio string based on current options
     *
     * @return string
     */
    protected function buildRadioString()
    {
        $parts = [
            ($this->isCurrent()) ? $this->pointer() : ' ',
            $this->label,
        ];

        $line = implode(' ', $parts);

        return $line . $this->getPaddingString($line);
    }

    /**
     * Get the padding string based on the length of the terminal/line
     *
     * @param string $line
     *
     * @return string
     */
    protected function getPaddingString($line)
    {
        $length = $this->util->system->width() - $this->lengthWithoutTags($line);

        return str_repeat(' ', $length);
    }

    /**
     * Get the pointer symbol
     *
     * @return string
     */
    protected function pointer()
    {
        return html_entity_decode("&#x276F;");
    }

    public function __toString()
    {
        if ($this->isFirst()) {
            return $this->buildRadioString();
        }

        if ($this->isLast()) {
            return $this->buildRadioString() . $this->util->cursor->left(10) . '<hidden>';
        }

        return $this->buildRadioString();
    }
}
