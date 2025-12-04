<?php

declare(strict_types=1);

namespace App\Services\Slots;

use App\Models\B2bSlot;
use App\Models\MobuleSlot;
use App\Models\Slots\Slot;

class SlotsService
{
    public function getImageByName(string $name): ?string
    {
        return $this->findSlotImage(
            str_replace(
                [' ', '.game'],
                '',
                $name
            )
        );
    }

    /**
     * @param B2bSlot|Slot|MobuleSlot $slot
     * @deprecated
     */
    public function getImage($slot): ?string
    {
        return $this->getImageByName($slot instanceof B2bSlot ? $slot->gm_url : $slot->title);
    }

    private function findSlotImage(string $name): ?string
    {
        $originalName = $name;
        $path = $this->getValidImagePath($name);

        if ($path === null) {
            $name = ucfirst(strtolower($name));
            $path = $this->getValidImagePath($name);

            if ($path === null) {
                $names = $this->generateUpperToLowerVariants($originalName);
                foreach ($names as $test) {
                    $path = $this->getValidImagePath($test);
                    if ($path !== null) {
                        return $path;
                    }
                }
            }

            if ($path === null) {
                $name = strtolower($name);
                $path = $this->getValidImagePath($name);
            }

            if ($path === null) {
                $names = $this->generateUpperToLowerVariants($name);
                foreach ($names as $test) {
                    $path = $this->getValidImagePath($test);
                    if ($path !== null) {
                        return $path;
                    }
                }
            }

            if ($path === null) {
                $name = preg_replace('/[^\p{L}]+/u', '', $name);
                $path = $this->getValidImagePath($name);
            }

            if ($path === null) {
                $names = $this->generateUpperToLowerVariants($name);
                foreach ($names as $test) {
                    $path = $this->getValidImagePath($test);
                    if ($path !== null) {
                        return $path;
                    }
                }
            }

            if ($path === null) {
                $name = str_replace("'", '', $originalName);
                $path = $this->getValidImagePath($name);
            }

            if ($path === null) {
                $name = str_replace(":", '', $name);
                $path = $this->getValidImagePath($name);
            }

            if ($path === null) {
                $name = strtolower($name);
                $path = $this->getValidImagePath($name);
            }
        }

        return $path;
    }

    public function getValidImagePath(string $name): ?string
    {
        $path = null;
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        foreach ($extensions as $extension) {
            foreach (['_', ''] as $uncacheParam) {
                if (file_exists(public_path('assets/image/slots/' . $name . $uncacheParam . '.' . $extension))) {
                    $path = '/assets/image/slots/' . $name . $uncacheParam . '.' . $extension;
                    break;
                }
            }
        }

        return $path;
    }


    private function generateUpperToLowerVariants(string $s, string $encoding = 'UTF-8', bool $onlyChanged = false): array
    {
        $len = mb_strlen($s, $encoding);
        $upperPositions = [];

        // находим позиции букв, которые в исходной строке — в верхнем регистре (и являются буквами)
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($s, $i, 1, $encoding);
            // проверяем, изменится ли символ при приведении к нижнему регистру (т.е. это буква)
            if (mb_strtolower($ch, $encoding) !== $ch && $ch === mb_strtoupper($ch, $encoding)) {
                $upperPositions[] = $i;
            }
        }

        $n = count($upperPositions);
        $variants = [];

        // перебираем все 2^n комбинаций
        for ($mask = 0; $mask < (1 << $n); $mask++) {
            // строим строку по символам (мб-символы)
            $chars = [];
            for ($i = 0; $i < $len; $i++) {
                $chars[$i] = mb_substr($s, $i, 1, $encoding);
            }

            // если в бите = 1 — делаем соответствующую заглавную букву строчной
            for ($j = 0; $j < $n; $j++) {
                if ($mask & (1 << $j)) {
                    $pos = $upperPositions[$j];
                    $chars[$pos] = mb_strtolower($chars[$pos], $encoding);
                }
            }

            $variant = implode('', $chars);

            if ($onlyChanged) {
                // пропускаем вариант, если он не отличается от исходного
                if ($variant === $s) {
                    continue;
                }
            }

            $variants[] = $variant;
        }

        return $variants;
    }
}