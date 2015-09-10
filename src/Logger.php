<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 21:36
 */
namespace Oasis\Mlib;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;
use Underscore\Types\Strings;

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

    /**
     * @var MonoLogger
     */
    static protected $logger = null;

    /**
     * @param string $logToPath     path to store log files
     * @param int    $minLogLevel   minimum log level
     * @param int    $errorLogLevel error level to trigger finger-crossed-handler
     */
    public static function init($logToPath, $minLogLevel = self::DEBUG, $errorLogLevel = self::ERROR)
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

        $ln_processor = "\\Oasis\\Mlib\\Logger::lnProcessor";

        $script_name = basename($_SERVER['SCRIPT_FILENAME'], ".php");
        $fs          = new AppendableFilesystem(new AppendableLocal($logToPath));

        $normal_log_key     = date('Ymd') . "/" . $script_name . ".log";
        $normal_log_fh      = $fs->appendStream($normal_log_key);
        $normal_log_handler = new StreamHandler($normal_log_fh, $minLogLevel);
        $normal_log_handler->setFormatter($line_formatter);
        $normal_log_handler->pushProcessor($ln_processor);

        $error_log_key     = date('Ymd') . "/" . $script_name . ".error";
        $error_log_fh      = $fs->appendStream($error_log_key);
        $error_log_handler = new StreamHandler($error_log_fh, $minLogLevel);
        $error_log_handler->setFormatter($line_formatter);
        $error_log_handler->pushProcessor($ln_processor);
        $auto_error_handler = new FingersCrossedHandler(
            $error_log_handler,
            new ErrorLevelActivationStrategy($errorLogLevel)
        );

        self::$logger = new MonoLogger(getmypid());
        self::$logger->pushHandler($normal_log_handler);
        self::$logger->pushHandler($auto_error_handler);

        if (PHP_SAPI == "cli") {
            $stdout_handler = new StreamHandler(STDOUT);
            $stdout_handler->setFormatter($colored_formatter);
            $stdout_handler->pushProcessor($ln_processor);
            self::$logger->pushHandler($stdout_handler);
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
            throw new \RuntimeException("Logger::init() should be called before using any logging function");
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

    public static function lnProcessor($record)
    {
        $callStack        = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);
        $self_encountered = false;
        foreach ($callStack as $trace) {
            if ($trace['file'] == __FILE__) {
                $self_encountered = true;
                continue;
            }
            elseif (!$self_encountered) continue;
            if (!Strings::endsWith($record['message'], "\n")) $record['message'] .= " ";
            $record['message'] .= "(" . basename($trace['file']) . ":" . $trace['line'] . ")";
            break;
        }

        return $record;
    }
}
