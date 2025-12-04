<?php

declare(strict_types=1);

namespace App\Helpers;

final class JsonFixer
{
    public static function sanitize(string $raw): string
    {
        $s = self::ensureUtf8(self::stripBom($raw));
        $s = self::stripJsonComments($s);
        $s = self::removeTrailingCommas($s);
        $s = self::singleToDoubleQuotedStrings($s);

        return self::quoteUnquotedKeys($s);
    }

    public static function decode(string $raw, bool $assoc = true, int $depth = 512): mixed
    {
        $sanitized = self::sanitize($raw);
        return json_decode($sanitized, $assoc, $depth, JSON_THROW_ON_ERROR);
    }

    public static function toValidJson(
        string $raw,
        int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ): string {
        $data = self::decode($raw, true);
        return json_encode($data, $flags | JSON_THROW_ON_ERROR);
    }

    private static function stripBom(string $s): string
    {
        $res = preg_replace('/^\xEF\xBB\xBF/', '', $s);
        return $res === null ? $s : $res;
    }

    private static function ensureUtf8(string $s): string
    {
        if (function_exists('mb_check_encoding') && !mb_check_encoding($s, 'UTF-8')) {
            if (function_exists('mb_convert_encoding')) {
                $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8, Windows-1251, ISO-8859-1, CP1252, ISO-8859-5');
            }
        }

        return $s;
    }

    private static function stripJsonComments(string $s): string
    {
        $out = '';
        $len = strlen($s);
        $inString = false;
        $stringDelim = '';
        $escape = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $s[$i];
            $next = $i + 1 < $len ? $s[$i + 1] : '';

            if ($inLineComment) {
                if ($ch === "\r" || $ch === "\n") {
                    $inLineComment = false;
                    $out .= $ch;
                }
                continue;
            }

            if ($inBlockComment) {
                if ($ch === '*' && $next === '/') {
                    $inBlockComment = false;
                    $i++; // пропустить '/'
                }
                continue;
            }

            if ($inString) {
                $out .= $ch;
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === $stringDelim) {
                    $inString = false;
                    $stringDelim = '';
                }
                continue;
            }

            // начало комментариев
            if ($ch === '/' && $next === '/') {
                $inLineComment = true;
                $i++;
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $inBlockComment = true;
                $i++;
                continue;
            }

            // начало строки
            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $stringDelim = $ch;
                $out .= $ch;
                continue;
            }

            $out .= $ch;
        }

        return $out;
    }

    /**
     * Убирает висячие запятые перед } или ] вне строк.
     */
    private static function removeTrailingCommas(string $s): string
    {
        $out = '';
        $len = strlen($s);
        $inString = false;
        $delim = '';
        $escape = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $s[$i];

            if ($inString) {
                $out .= $ch;
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === $delim) {
                    $inString = false;
                    $delim = '';
                }
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $delim = $ch;
                $out .= $ch;
                continue;
            }

            if ($ch === ',') {
                $j = $i + 1;
                while ($j < $len && ctype_space($s[$j])) {
                    $j++;
                }
                if ($j < $len && ($s[$j] === '}' || $s[$j] === ']')) {
                    // пропускаем запятую
                    continue;
                }
            }

            $out .= $ch;
        }

        return $out;
    }

    /**
     * Конвертирует одинарно-кавычковые строки в двойные с корректными экранированиями.
     */
    private static function singleToDoubleQuotedStrings(string $s): string
    {
        $out = '';
        $len = strlen($s);
        $inString = false;
        $delim = '';
        $escape = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $s[$i];

            if (!$inString && $ch === "'") {
                // Начинаем single-quoted строку: перепишем её в double-quoted
                $i++;
                $content = '';
                $esc = false;
                while ($i < $len) {
                    $c = $s[$i];

                    if ($esc) {
                        // Обрабатываем известные escape-последовательности
                        switch ($c) {
                            case "'":
                                $content .= "'"; // \' -> '
                                break;
                            case '"':
                                $content .= '\"'; // \" -> \"
                                break;
                            case '\\':
                                $content .= '\\\\'; // \\ -> \\
                                break;
                            case 'n':
                            case 'r':
                            case 't':
                            case 'b':
                            case 'f':
                            case '/':
                                $content .= '\\' . $c; // сохраняем как escape
                                break;
                            case 'u':
                                // переносим \uXXXX как есть, если корректно
                                $hex = substr($s, $i + 1, 4);
                                if (preg_match('/^[0-9A-Fa-f]{4}$/', $hex)) {
                                    $content .= '\\u' . $hex;
                                    $i += 4;
                                } else {
                                    // неизвестный/битый \u — экранируем буквально
                                    $content .= '\\u';
                                }
                                break;
                            default:
                                // неизвестный escape — экранируем бэкслеш буквально
                                $content .= '\\\\' . $c;
                                break;
                        }
                        $esc = false;
                        $i++;
                        continue;
                    }

                    if ($c === '\\') {
                        $esc = true;
                        $i++;
                        continue;
                    }

                    if ($c === "'") {
                        // конец single-quoted
                        break;
                    }

                    // Экранируем проблемные символы для JSON double-quoted
                    $ord = ord($c);
                    if ($c === '"') {
                        $content .= '\"';
                    } elseif ($c === "\n") {
                        $content .= '\\n';
                    } elseif ($c === "\r") {
                        $content .= '\\r';
                    } elseif ($c === "\t") {
                        $content .= '\\t';
                    } elseif ($ord >= 0 && $ord <= 0x1F) {
                        $content .= sprintf('\\u%04X', $ord);
                    } else {
                        $content .= $c;
                    }
                    $i++;
                }

                // Закрывающая одинарная кавычка (если была) уже на s[$i]
                $out .= '"' . $content . '"';
                $inString = false;
                $delim = '';
                $escape = false;
                continue;
            }

            // Обычная логика для других строк
            if ($inString) {
                $out .= $ch;
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === $delim) {
                    $inString = false;
                    $delim = '';
                }
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $delim = $ch;
                $out .= $ch;
                continue;
            }

            $out .= $ch;
        }

        return $out;
    }

    /**
     * Кавычит некавыченные ключи в объектах: { key: ... } или , key: ...
     */
    private static function quoteUnquotedKeys(string $s): string
    {
        $out = '';
        $len = strlen($s);
        $inString = false;
        $delim = '';
        $escape = false;
        $lastSig = ''; // последний значимый символ (вне строк)

        for ($i = 0; $i < $len; $i++) {
            $ch = $s[$i];

            if ($inString) {
                $out .= $ch;
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === $delim) {
                    $inString = false;
                    $delim = '';
                }
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $delim = $ch;
                $out .= $ch;
                continue;
            }

            // Возможное начало ключа после { или ,
            if (preg_match('/[A-Za-z_]/', $ch) && ($lastSig === '{' || $lastSig === ',')) {
                // Считываем идентификатор
                $id = $ch;
                $j = $i + 1;
                while ($j < $len && preg_match('/[A-Za-z0-9_\-]/', $s[$j])) {
                    $id .= $s[$j];
                    $j++;
                }
                // Пропускаем пробелы
                $t = $j;
                while ($t < $len && ctype_space($s[$t])) {
                    $t++;
                }
                if ($t < $len && $s[$t] === ':') {
                    // Это ключ — заменяем на "ключ":
                    $out .= '"' . $id . '"';
                    // переносим промежуток и двоеточие
                    $out .= substr($s, $j, $t - $j) . ':';
                    $i = $t; // продолжим после двоеточия
                    $lastSig = ':';
                    continue;
                }
                // не ключ — откатываемся к обычной записи
            }

            $out .= $ch;

            // Обновляем последний значимый символ
            if (!ctype_space($ch)) {
                $lastSig = $ch;
            }
        }

        return $out;
    }
}