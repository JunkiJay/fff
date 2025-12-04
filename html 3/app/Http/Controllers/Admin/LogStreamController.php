<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogStreamController extends Controller
{
    public function index()
    {
        return view('admin.logs');
    }

    public function stream(Request $request)
    {
        $file = storage_path('logs/laravel.log');

        $response = new StreamedResponse(function () use ($file) {
            if (!file_exists($file)) {
                touch($file);
            }

            ignore_user_abort(true);
            @set_time_limit(0);

            // "Разгон" для отключения буферов
            echo ":" . str_repeat(" ", 2048) . "\n\n";
            @ob_flush();
            @flush();

            // Начинаем «с хвоста» (не засыпать клиента прошлым)
            clearstatcache(true, $file);
            $lastPos = max(filesize($file) - 200 * 1024, 0); // последние ~200 КБ при коннекте

            while (!connection_aborted()) {
                clearstatcache(true, $file);
                $size = @filesize($file) ?: 0;

                // Файл могли ротировать/обрезать
                if ($size < $lastPos) {
                    $lastPos = 0;
                }

                if ($size > $lastPos) {
                    $fh = @fopen($file, 'r');
                    if ($fh) {
                        fseek($fh, $lastPos);
                        while (!feof($fh)) {
                            $line = fgets($fh);
                            if ($line === false) {
                                break;
                            }
                            $payload = json_encode(
                                ['line' => rtrim($line, "\r\n")],
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            );
                            echo "event: log\n";
                            echo "data: {$payload}\n\n";
                        }
                        $lastPos = ftell($fh);
                        fclose($fh);
                        @ob_flush();
                        @flush();
                    }
                }

                // keep-alive, чтобы соединение не засыпало
                echo ": keep-alive\n\n";
                @ob_flush();
                @flush();

                sleep(1);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('X-Accel-Buffering', 'no'); // для Nginx, если проксируете через него
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}