<?php
namespace Infrastructure\Logging;

class LogService {
  private static string $LOG_FOLDER = 'storage';
  private static string $LOG_FILENAME = 'system-api.log';
  private static string $logFile;

  public static function info(string $message): void {
    self::writeLog('INFO', $message);
  }

  public static function error(string $message): void {
    self::writeLog('ERROR', $message);
  }

  public static function debug(string $message): void {
    self::writeLog('DEBUG', $message);
  }

  private static function writeLog(string $level, string $message): void {
    $applicationRootDirectory = dirname(__DIR__, 3);

    $logDir = $applicationRootDirectory . DIRECTORY_SEPARATOR . self::$LOG_FOLDER;

    if (!is_dir($logDir)) 
      mkdir($logDir, 0755, true);

    self::$logFile = $logDir . DIRECTORY_SEPARATOR . self::$LOG_FILENAME;

    if (!file_exists(self::$logFile)) {
      touch(self::$logFile);
      chmod(self::$logFile, 0644);
    }

    $logEntry = sprintf('[%s] [%s] %s%s', date('Y-m-d H:i:s'), $level, $message, PHP_EOL);

    try {
      $bytesWritten = @file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
      if ($bytesWritten === false) {
        throw new \RuntimeException('Failed to write log entry');
      }
    } catch (\Exception $e) {
      if (PHP_OS_FAMILY === 'Windows') 
        syslog(LOG_ERR, 'Failed to write to log file: ' . $e->getMessage());
       else 
        error_log('Failed to write to log file: ' . $e->getMessage());

      throw $e;
    }
  }
}
