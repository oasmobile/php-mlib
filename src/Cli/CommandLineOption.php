<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-16
 * Time: 16:51
 */

namespace Oasis\Mlib\Cli;

class CommandLineOption
{
    protected $name;
    protected $followable       = true;
    protected $mustBeFollowed   = false;
    protected $isOptional       = true;
    protected $defaultValue     = null;
    protected $aliasTo          = [];
    protected $description      = '';
    protected $valueDescription = '';

    protected $value              = null;
    protected $isSetInCommandLine = false;

    function __construct($name)
    {
        $this->name      = $name;
        $this->aliasTo[] = $name;
    }

    function __call($name, $arguments)
    {
        call_user_func_array([CommandLineArgParser::parser(), $name], $arguments);
    }

    /**
     * This is simply a helper function to make IDE happy
     *
     * @return CommandLineArgParser
     */
    public function end()
    {
        return CommandLineArgParser::parser();
    }

    public function cannotBeFollowed()
    {
        $this->followable = false;

        return $this;
    }

    public function requiresValue()
    {
        $this->mustBeFollowed = true;

        return $this;
    }

    public function isMandatory()
    {
        $this->isOptional = false;

        return $this;
    }

    public function hasDefaultValue($val)
    {
        $this->defaultValue = $val;

        return $this;
    }

    public function interprets($name)
    {
        return in_array($name, $this->aliasTo);
    }

    public function validate()
    {
        if (!$this->isOptional && !$this->isSetInCommandLine) {
            throw new CommandLineParsingException("Mandatory option <{$this->name}> is missing!");
        }
        if ($this->isSetInCommandLine
            && $this->mustBeFollowed
            && $this->value === null
        ) {
            throw new CommandLineParsingException("Option <{$this->name}> is not followed by a value!");
        }
    }

    public function aliasTo($alias)
    {
        $this->aliasTo[] = $alias;
        $this->aliasTo   = array_values($this->aliasTo);

        return $this;
    }

    public function getAllAliases()
    {
        return $this->aliasTo;
    }

    public function setValue($val)
    {
        if (!$this->followable) {
            throw new CommandLineParsingException(
                "Option <{$this->name}> should not be followed by a value. Value got: $val"
            );
        }
        $this->value = $val;
    }

    public function getValue()
    {
        if ($this->value === null) {
            if ($this->defaultValue !== null) {
                return $this->defaultValue;
            }
            if ($this->isSetInCommandLine) {
                return false;
            }
        }

        return $this->value;
    }

    public function isActivated()
    {
        return $this->isSetInCommandLine;
    }

    public function activate()
    {
        $this->isSetInCommandLine = true;
    }

    /**
     * @return boolean
     */
    public function isFollowable()
    {
        return $this->followable;
    }

    /**
     * @param string $description
     *
     * @return CommandLineOption
     */
    public function usage($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     */
    public function isOptionalOption()
    {
        return $this->isOptional;
    }

    /**
     * @return boolean
     */
    public function isValueRequired()
    {
        return $this->mustBeFollowed;
    }

    /**
     * @return string
     */
    public function getValueDescription()
    {
        return $this->valueDescription;
    }

    /**
     * @param string $valueDescription
     */
    public function setValueDescription($valueDescription)
    {
        $this->valueDescription = $valueDescription;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
