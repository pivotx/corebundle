<?php

namespace PivotX\Component\Formats;


/**
 */
interface FormatInterface
{
    /**
     * Perform the actual format
     *
     * @param array $arguments arguments for formatting
     * @return mixed           format result
     */
    public function format($in, $arguments = array());

    /**
     * Get the name of the format
     *
     * @return string format name
     */
    public function getName();

    /**
     * Get the group of the format
     *
     * @return string group name
     */
    public function getGroup();

    /**
     * Get a developer description of the format
     *
     * @return string format description
     */
    public function getDescription();

    /**
     * Get a code example
     *
     * @return string code example
     */
    public function getCodeExample();
}
