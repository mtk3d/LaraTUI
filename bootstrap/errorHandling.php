<?php

use Symfony\Component\VarDumper\VarDumper;

function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    VarDumper::dump([
        'error_number' => $errno,
        'error_message' => $errstr,
        'error_file' => $errfile,
        'error_line' => $errline,
    ]);
}

function customExceptionHandler($exception)
{
    VarDumper::dump([
        'exception_message' => $exception->getMessage(),
        'exception_file' => $exception->getFile(),
        'exception_line' => $exception->getLine(),
        'exception_trace' => $exception->getTraceAsString(),
    ]);
}
