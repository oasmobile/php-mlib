<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-16
 * Time: 14:37
 */

namespace Oasis\Mlib\Cli;

use Oasis\Mlib\MUtils;

/**
 * Class CommandLineArgParser
 *
 * Used to parse command line arguments
 *
 * The default parameters supported are:
 * --help       shows help content to stdout and exit with 0
 * --daemonize  runs script in daemon mode and exit the main process with 0
 *
 * @package Oasis\Mlib\Cli
 */
class CommandLineArgParser
{
    /** @var CommandLineOption[] */
    protected $options            = [];
    protected $disabledDefaults   = [];
    protected $helpContent        = '';
    protected $remainingArguments = [];

    public static function parser()
    {
        static $inst = null;
        if (!$inst) {
            $inst = new static;
        }

        return $inst;
    }

    public function disableDefault($name)
    {
        $this->disabledDefaults[] = $name;

        return $this;
    }

    public function add($optName)
    {
        $opt             = new CommandLineOption($optName);
        $this->options[] = $opt;

        return $opt;
    }

    /**
     * @param string $helpContent
     *
     * @return CommandLineArgParser
     */
    public function setHelpContent($helpContent)
    {
        $this->helpContent = $helpContent;

        return $this;
    }

    public function run($argc = null, $argv = null)
    {
        $argc        = $argc === null ? $_SERVER['argc'] : $argc;
        $argv        = $argv === null ? $_SERVER['argv'] : $argv;
        $opt_stopped = false;
        /** @var CommandLineOption[] $option_stack */
        $option_stack = [];

        if (!in_array("help", $this->disabledDefaults)) {
            $this->add('help')->cannotBeFollowed()->usage("Show this help.");
        }
        if (!in_array("daemonize", $this->disabledDefaults)) {
            $this->add('daemonize')->cannotBeFollowed()->usage("Run the script in daemonized mode.");
        }

        try {
            for ($i = 1; $i < $argc; ++$i) {
                $val = $argv[$i];

                if (!$opt_stopped && $val === "--") {
                    $opt_stopped = true;
                    continue;
                }

                $optName = $optValue = null;
                if (!$opt_stopped && strlen($val) > 1) {
                    if (MUtils::stringStartsWith($val, "--")) {
                        $optName = substr($val, 2);
                        if (($pos = strpos($optName, "=")) !== false) {
                            $optValue = substr($optName, $pos + 1);
                            $optName  = substr($optName, 0, $pos);
                        }
                    }
                    elseif (MUtils::stringStartsWith($val, "-")) {
                        $optName = substr($val, 1);
                        if (strlen($optName) > 1) {
                            $optValue = substr($optName, 1);
                            $optName  = substr($optName, 0, 1);
                        }
                    }
                }

                /** @var CommandLineOption $currentOpt */
                if ($optName !== null) {
                    // this is an option
                    $currentOpt = $this->getOptionObject($optName);
                    if (!$currentOpt) {
                        throw new CommandLineParsingException("Command line option <$optName> is not recognized!");
                    }
                    $currentOpt->activate();
                    if ($optValue !== null) {
                        $currentOpt->setValue($optValue);
                        $prev_opt = null;
                    }
                    elseif ($currentOpt->isFollowable()) {
                        $option_stack[] = $currentOpt;
                    }
                }
                else {
                    // not an option
                    if (!$opt_stopped && $option_stack) {
                        $currentOpt = array_pop($option_stack);
                        $currentOpt->setValue($val);
                    }
                    else {
                        $this->remainingArguments[] = $val;
                    }
                }
            }
            if (!in_array("help", $this->disabledDefaults)
                && $this->has('help')
            ) {
                $this->showHelpAndExit();
            }

            foreach ($this->options as $option) {
                $option->validate();
            }
        } catch (CommandLineParsingException $e) {
            $this->showErrorAndExit($e->getMessage());
        }

        if (!in_array("daemonize", $this->disabledDefaults)
            && $this->has('daemonize')
        ) {
            $this->daemonize();
        }

        return $this;
    }

    public function get($key)
    {
        $opt = $this->getOptionObject($key);
        if ($opt) {
            return $opt->getValue();
        }
        else {
            return null;
        }
    }

    public function has($key)
    {
        $opt = $this->getOptionObject($key);
        if ($opt && $opt->isActivated()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function debug()
    {
        foreach ($this->options as $option) {
            if ($option->isActivated()) {
                $key = $option->getName();
                mdebug("Option <$key> set to " . json_encode($option->getValue()));
            }
        }
        foreach ($this->remainingArguments as $arg) {
            mdebug("Remaining arg: $arg");
        }
    }

    /**
     * @return array
     */
    public function getRemainingArguments()
    {
        return $this->remainingArguments;
    }

    protected function getOptionObject($optName)
    {
        foreach ($this->options as $option) {
            if ($option->interprets($optName)) {
                return $option;
            }
        }

        return null;
    }

    protected function showErrorAndExit($msg)
    {
        file_put_contents('php://stderr', $msg . PHP_EOL);
        exit(1);
    }

    protected function showHelpAndExit()
    {
        if ($this->helpContent) {
            echo $this->helpContent . PHP_EOL;
        }
        else {
            $auto_guide  = $this->getAutoGuide();
            $helpContent = <<<NOHELP
Below is the auto-generated help info:

$auto_guide

NOHELP;
            echo $helpContent . PHP_EOL;
        }

        exit(0);
    }

    protected function getAutoGuide()
    {
        $break_at  = 30;
        $col_width = 80;
        $ret       = '';
        foreach ($this->options as $option) {
            $aliases   = $option->getAllAliases();
            $name_part = '';
            foreach ($aliases as $optName) {
                $optName = "-" . $optName;
                if (strlen($optName) > 2) {
                    $optName = "-" . $optName;
                }
                $name_part .= ", " . $optName;
            }
            $name_part = trim($name_part, ", ");
            if ($option->isFollowable()) {
                $name_part .= " ";
                $name_part .= $option->isValueRequired() ? "<" : "[";
                $name_part .= $option->getValueDescription() ? : "value";
                $name_part .= $option->isValueRequired() ? ">" : "]";
            }

            $ret .= "  " . $name_part;
            $remaining = $break_at - strlen($name_part);
            if ($remaining > 3) {
                $ret .= str_repeat(" ", $remaining);
            }
            else {
                $ret .= PHP_EOL . "  " . str_repeat(" ", $break_at);
            }

            $desc               = $option->getDescription();
            $desc               = $desc ? : "Undocumented parameter";
            $current_line_count = 0;
            while (strlen($desc) > 0) {
                $c    = $desc[0];
                $desc = substr($desc, 1);
                if ($current_line_count + $break_at > $col_width
                    && !preg_match('/[a-zA-Z0-9]/', $c)
                ) {
                    $current_line_count = 0;
                    $ret .= $c;
                    $ret .= PHP_EOL . "  " . str_repeat(" ", $break_at);
                    $c = '';
                }
                $ret .= $c;
                $current_line_count++;
            }
            if (!$option->isOptionalOption()) {
                $ret .= " (Mandatory)";
            }
            $ret .= PHP_EOL;
        }

        return $ret;
    }

    protected function daemonize()
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            echo("Cannot enter daemon mode. Error: " . pcntl_get_last_error());
            exit(1);
        }
        elseif ($pid > 0) {
            // in parent
            exit(0);
        }
        else {
            // in child
        }
    }

}
