<?php

namespace App\Console\Commands;

use App\Models\SlotSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PricessLogCommand extends Command
{
    protected $signature = 'log:process {--file=} {--follow : Следить за новыми строками (аналог tail -f)}';
    protected $description = 'Построчная обработка storage/logs/laravel.log';

    public Collection $sessions;
    public Collection $users;
    public Carbon $dateFrom;

    public function handle(): int
    {
        $this->sessions = collect();
        $this->users = collect();
        $this->dateFrom = Carbon::make('2025-08-15 17:05');
        $path = $this->option('file') ?? storage_path('logs/laravel.log');

        if (!is_readable($path)) {
            $this->error("Файл недоступен: {$path}");
            return self::FAILURE;
        }

        $follow = (bool)$this->option('follow');
        $this->processFile($path, $follow);

        return self::SUCCESS;
    }

    private function processFile(string $path, bool $follow): void
    {
        $fp = fopen($path, 'r');
        if (!$fp) {
            $this->error('Не удалось открыть файл');
            return;
        }

        // Если следим за новыми строками — переходим в конец файла
        if ($follow) {
            fseek($fp, 0);
        }

        while (true) {
            $line = fgets($fp);

            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            $this->handleLogLine($line);
        }

        fclose($fp);
    }

    private function handleLogLine(string $line): void
    {
        if (str_contains($line, 'Module callback:')) {
            $json = $this->extractJsonAfterMarker($line);
            $date = $this->extractLogDatetime($line);
            if ($json && $date) {
                $data = json_decode($json);
                if (isset($data[0]) && ($data[0] === 'deposit.win' || $data[0] === 'withdraw.bet')) {
                    $slotSession = $this->sessions->get($data[1]->session) ?? SlotSession::find($data[1]->session);

                    if (!$slotSession) {
                        return;
                    }
                    $this->sessions->offsetSet($data[1]->session, $slotSession);

                    if ($slotSession->user_id !== 123115) {
                        return;
                    }
                    $user = $this->users->get($slotSession->user_id) ?? User::find($slotSession->user_id);

                    $date = Carbon::make($date);

                    if (!$date->isAfter($this->dateFrom)) {
                        return;
                    }

                    dd($data);
                    if ($user === null) {
                        return;
                    }
                    $this->users->offsetSet($user->id, $user);
                    $amount = $data[1]->amount / 100;

                    dd($amount);

                    dd($data[1]);
                }
            }
            return;
        }
    }

    public function extractJsonAfterMarker(string $line, string $marker = 'Module callback:'): ?string
    {
        $pos = strpos($line, $marker);
        if ($pos === false) return null;
        $json = trim(substr($line, $pos + strlen($marker)));

        return json_validate($json) ? $json : null;
    }

    public function extractLogDatetime(string $line): ?string
    {
        // Берём дату/время в первых квадратных скобках в начале строки
        if (preg_match('/^\[([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})]/', $line, $m)) {
            return $m[1]; // например: 2025-08-14 10:39:40
        }
        return null;
    }


}