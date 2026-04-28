#!/usr/bin/env php
<?php

declare(strict_types=1);

use Smalot\PdfParser\Parser;

require __DIR__.'/../alt_autoload.php-dist';

const DEFAULT_TIMEOUT_SECONDS = 45;
const STDERR_SNIPPET_LIMIT = 4000;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This tool must be run from CLI.\n");
    exit(1);
}

$argvCopy = $argv;
array_shift($argvCopy);
$singleFile = extractOptionValue($argvCopy, '--single');

if (null !== $singleFile) {
    runSingle($singleFile);

    return;
}

$timeoutSeconds = (int) (extractOptionValue($argvCopy, '--timeout') ?? (string) DEFAULT_TIMEOUT_SECONDS);
$paths = array_values(array_filter($argvCopy, static fn (string $arg): bool => '' !== trim($arg)));

if ([] === $paths) {
    fwrite(STDERR, "Usage: php dev-tools/diagnose-parser.php [--timeout <seconds>] <pdf-path> [<pdf-path> ...]\n");
    fwrite(STDERR, "       php dev-tools/diagnose-parser.php --single <pdf-path>\n");
    exit(1);
}

foreach ($paths as $path) {
    $result = runIsolated($path, $timeoutSeconds);
    echo json_encode($result, JSON_UNESCAPED_SLASHES)."\n";
}

function runSingle(string $path): void
{
    $startedAt = microtime(true);
    $normalizedPath = normalizePath($path);

    if (!is_file($normalizedPath)) {
        echo json_encode([
            'file' => $path,
            'status' => 'file_not_found',
            'error' => 'Input file does not exist.',
        ], JSON_UNESCAPED_SLASHES)."\n";
        exit(3);
    }

    try {
        $document = (new Parser())->parseFile($normalizedPath);
        $pages = $document->getPages();

        echo json_encode([
            'file' => $path,
            'status' => 'ok',
            'pages' => count($pages),
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'peak_memory_bytes' => memory_get_peak_usage(true),
        ], JSON_UNESCAPED_SLASHES)."\n";
        exit(0);
    } catch (Throwable $throwable) {
        echo json_encode([
            'file' => $path,
            'status' => 'parser_exception',
            'error_class' => $throwable::class,
            'error' => $throwable->getMessage(),
            'error_file' => $throwable->getFile(),
            'error_line' => $throwable->getLine(),
            'trace_head' => trim(limitString($throwable->getTraceAsString(), STDERR_SNIPPET_LIMIT)),
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'peak_memory_bytes' => memory_get_peak_usage(true),
        ], JSON_UNESCAPED_SLASHES)."\n";
        exit(2);
    }
}

/**
 * @return array<string, mixed>
 */
function runIsolated(string $path, int $timeoutSeconds): array
{
    $command = [
        PHP_BINARY,
        '-d',
        'display_errors=1',
        '-d',
        'log_errors=0',
        __FILE__,
        '--single',
        $path,
    ];

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, getcwd());
    if (!is_resource($process)) {
        return [
            'file' => $path,
            'status' => 'runner_error',
            'error' => 'Unable to start child process.',
        ];
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $stdout = '';
    $stderr = '';
    $startTime = microtime(true);
    $timedOut = false;
    $lastStatus = null;

    while (true) {
        $lastStatus = proc_get_status($process);
        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);

        if (!$lastStatus['running']) {
            break;
        }

        if ((microtime(true) - $startTime) > $timeoutSeconds) {
            $timedOut = true;
            proc_terminate($process, 9);
            $lastStatus = proc_get_status($process);
            break;
        }

        usleep(20000);
    }

    $stdout .= (string) stream_get_contents($pipes[1]);
    $stderr .= (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);
    $stdoutTrimmed = trim($stdout);
    $decoded = json_decode($stdoutTrimmed, true);

    if (is_array($decoded)) {
        $decoded['elapsed_ms_total'] = $elapsedMs;
        $decoded['child_exit_code'] = $exitCode;
        $decoded['child_signaled'] = (bool) (($lastStatus['signaled'] ?? false));
        $decoded['child_signal'] = $lastStatus['termsig'] ?? null;
        $decoded['child_stderr'] = trim(limitString($stderr, STDERR_SNIPPET_LIMIT));

        return $decoded;
    }

    return [
        'file' => $path,
        'status' => $timedOut ? 'timeout' : 'child_failure',
        'error' => $timedOut ? 'Timed out while parsing file.' : 'Child process failed before returning JSON payload.',
        'elapsed_ms_total' => $elapsedMs,
        'child_exit_code' => $exitCode,
        'child_signaled' => (bool) (($lastStatus['signaled'] ?? false)),
        'child_signal' => $lastStatus['termsig'] ?? null,
        'child_stdout' => trim(limitString($stdout, STDERR_SNIPPET_LIMIT)),
        'child_stderr' => trim(limitString($stderr, STDERR_SNIPPET_LIMIT)),
    ];
}

function normalizePath(string $path): string
{
    if (str_starts_with($path, '/')) {
        return $path;
    }

    return (string) (getcwd().'/'.$path);
}

function extractOptionValue(array &$args, string $option): ?string
{
    $index = array_search($option, $args, true);
    if (false === $index) {
        return null;
    }

    $valueIndex = $index + 1;
    $value = $args[$valueIndex] ?? null;
    unset($args[$index], $args[$valueIndex]);
    $args = array_values($args);

    return is_string($value) ? $value : null;
}

function limitString(string $value, int $maxLength): string
{
    if (strlen($value) <= $maxLength) {
        return $value;
    }

    return substr($value, 0, $maxLength)."\n...[truncated]";
}
