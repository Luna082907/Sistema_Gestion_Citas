<?php

declare(strict_types=1);

namespace App\Domain;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;

final class SlotGenerator{
    public function generate(string $start = '08:00',string $end = '12:00', int $minutes = 20):array{
        if ($minutes <=0 || $minutes > 720){
            throw new InvalidArgumentException("La duración debe estar entre 1 y 720 minutos");
        }

        $current = $this->parseTime($start);
        $limit = $this->parseTime($end);
        if ($current >= $limit){
            throw new InvalidArgumentException("La hora final debe ser posterior a la hora incial");
        }

        $interval = new DateInterval('PT' .$minutes .'M');
        $slots = [];

        while ($current < $limit){
            $next = $current ->add($interval);
            if ($next > $limit){
                break;
            }

            $slots[] = $current->format('H:i:s');
            $current = $next;
        }

        return $slots;
    }

    private function parseTime(string $time): DateTimeImmutable{
        $normalized = strlen($time) === 5 ? $time . ':00': $time;
        $parsed = DateTimeImmutable::createFromFormat('!H:i:s', $normalized);

        if ($parsed === false || $parsed->format('H:i:s') !== $normalized){
            throw new InvalidArgumentException("La hora {$time} no tiene un formato valido");
        }

        return $parsed;
    }
}

?>