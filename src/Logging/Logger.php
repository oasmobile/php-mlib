<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 21:36
 */
namespace Oasis\Mlib\Logging;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;
use Oasis\Mlib\MUtils;

class Logger
{
    const DEBUG     = MonoLogger::DEBUG;
    const INFO      = MonoLogger::INFO;
    const NOTICE    = MonoLogger::NOTICE;
    const WARNING   = MonoLogger::WARNING;
    const ERROR     = MonoLogger::ERROR;
    const CRITICAL  = MonoLogger::CRITICAL;
    const ALERT     = MonoLogger::ALERT;
    const EMERGENCY = MonoLogger::EMERGENCY;

    static protected $logToPath               = '';
    static protected $minLogLevel             = self::DEBUG;
    static protected $isConsoleHandlerEnabled = false;
    /**
     * @var MonoLogger
     */
    static protected $logger = null;

    /** @var StreamHandler */
    static protected $console_handler = null;
    /** @var StreamHandler */
    static protected $file_handler = null;
    /** @var FingersCrossedHandler */
    static protected $error_handler = null;

    /**
     * @param string $logToPath         path to store log files
     * @param int    $minLogLevel       minimum log level
     * @param int    $errorLogLevel     error log level
     * @param int    $errorTriggerLevel error level to trigger finger-crossed-handler
     */
    public static function init($logToPath,
                                $minLogLevel = self::DEBUG,
                                $errorLogLevel = self::WARNING,
                                $errorTriggerLevel = self::ERROR)
    {
        $datetime_format = "Ymd-His";
        $output_format   = "[%channel%] %datetime% | %level_name% | %message% %context% %extra%\n";
        $line_formatter  = new LineFormatter(
            $output_format,
            $datetime_format,
            true
        );
        $line_formatter->includeStacktraces();
        $colored_formatter = new ColoredLineFormatter(
            new DefaultScheme(),
            $output_format,
            $datetime_format,
            true,
            true
        );
        $colored_formatter->includeStacktraces();

        $ln_processor = "\\Oasis\\Mlib\\Logging\\Logger::lnProcessor";

        self::$logToPath   = $logToPath;
        self::$minLogLevel = $minLogLevel;

        self::$logger = new MonoLogger('mlib');

        if (PHP_SAPI == "cli") {
            self::$console_handler = new StreamHandler(fopen('php://stdout', 'w'), $minLogLevel);
            self::$console_handler->setFormatter($colored_formatter);
            self::$console_handler->pushProcessor($ln_processor);
            self::$logger->pushHandler(self::$console_handler);
            self::$isConsoleHandlerEnabled = true;
        }

        if ($logToPath) {
            try {
                $fs = new AppendableFilesystem(new AppendableLocal($logToPath));

                $script_name = basename($_SERVER['SCRIPT_FILENAME'], ".php");

                $normal_log_key     = date('Ymd') . "/" . $script_name . ".log";
                $normal_log_fh      = $fs->appendStream($normal_log_key);
                self::$file_handler = new StreamHandler($normal_log_fh, $minLogLevel);
                self::$file_handler->setFormatter($line_formatter);
                self::$file_handler->pushProcessor($ln_processor);
                self::$logger->pushHandler(self::$file_handler);

                $error_log_key       = date('Ymd') . "/" . $script_name . ".error";
                $error_log_fh        = $fs->appendStream($error_log_key);
                self::$error_handler = new StreamHandler($error_log_fh, $errorLogLevel);
                self::$error_handler->setFormatter($line_formatter);
                self::$error_handler->pushProcessor($ln_processor);
                $auto_error_handler = new FingersCrossedHandler(
                    self::$error_handler,
                    new ErrorLevelActivationStrategy($errorTriggerLevel),
                    0, /* buffer size, 0 means no limit */
                    true, /* bubbles */
                    false /* stop bufferring on strategy activated */
                );
                self::$logger->pushHandler($auto_error_handler);

            } catch (\LogicException $e) {
                self::$logger->error($e->getMessage());
            }
        }
    }

    public static function debug($msg, $context = [])
    {
        self::log(self::DEBUG, $msg, $context);
    }

    public static function info($msg, $context = [])
    {
        self::log(self::INFO, $msg, $context);
    }

    public static function notice($msg, $context = [])
    {
        self::log(self::NOTICE, $msg, $context);
    }

    public static function warning($msg, $context = [])
    {
        self::log(self::WARNING, $msg, $context);
    }

    public static function error($msg, $context = [])
    {
        self::log(self::ERROR, $msg, $context);
    }

    public static function critical($msg, $context = [])
    {
        self::log(self::CRITICAL, $msg, $context);
    }

    public static function alert($msg, $context = [])
    {
        self::log(self::ALERT, $msg, $context);
    }

    public static function emergency($msg, $context = [])
    {
        self::log(self::EMERGENCY, $msg, $context);
    }

    public static function log($level, $msg, $context = [])
    {
        if (!self::$logger instanceof MonoLogger) {
            //throw new \RuntimeException("Logger::init() should be called before using any logging function");
            self::init("");
        }
        self::$logger->log($level, $msg, $context);
    }

    public static function getExceptionDebugInfo(\Exception $exception)
    {
        return
            "Exception info: " . $exception->getMessage()
            . PHP_EOL
            . ("code = " . $exception->getCode() . ", at " . $exception->getFile() . ":" . $exception->getLine())
            . PHP_EOL
            . $exception->getTraceAsString()
            . PHP_EOL;
    }

    public static function lnProcessor(array $record)
    {
        $callStack        = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);
        $self_encountered = false;
        foreach ($callStack as $trace) {
            if ($trace['file'] == __FILE__) {
                $self_encountered = true;
                continue;
            }
            elseif (!$self_encountered) {
                continue;
            }
            if (!MUtils::stringEndsWith($record['message'], "\n")) {
                $record['message'] .= " ";
            }
            $record['message'] .= "(" . basename($trace['file']) . ":" . $trace['line'] . ")";
            break;
        }
        $record['channel'] = getmypid();

        return $record;
    }

    /**
     * @return string
     */
    public static function getLogToPath()
    {
        return self::$logToPath;
    }

    /**
     * @return int
     */
    public static function getMinLogLevel()
    {
        return self::$minLogLevel;
    }

    /**
     * @param int $minLogLevel
     */
    public static function setMinLogLevel($minLogLevel)
    {
        if (self::$console_handler
            && self::$isConsoleHandlerEnabled
        ) {
            self::$console_handler->setLevel($minLogLevel);
        }
        if (self::$file_handler) {
            self::$file_handler->setLevel($minLogLevel);
        }
        if (self::$error_handler) {
            self::$error_handler->setLevel($minLogLevel);
        }
        self::$minLogLevel = $minLogLevel;
    }

    /**
     * @return boolean
     */
    public static function isIsConsoleHandlerEnabled()
    {
        return self::$isConsoleHandlerEnabled;
    }

    /**
     * @param boolean $isConsoleHandlerEnabled
     */
    public static function enableConsoleLog($isConsoleHandlerEnabled = true)
    {
        if (self::$isConsoleHandlerEnabled == $isConsoleHandlerEnabled) {
            return;
        }
        if (!self::$console_handler) {
            return;
        }

        if (self::$isConsoleHandlerEnabled) {
            self::$console_handler->setLevel(self::EMERGENCY + 1); // biggest and impossible log level
        }
        else {
            self::$console_handler->setLevel(self::$minLogLevel);
        }

        self::$isConsoleHandlerEnabled = $isConsoleHandlerEnabled;
    }

}
