<?php

namespace App\Service;

class MathManager
{
    public function getPercents($total, $validated, $progress, $review, $none)
    {
        $validatedPercent = ($validated != 0)
          ? $this->roundToOne($validated/$total*100)
          : 0;

        $progressPercent = ($progress != 0)
          ? $this->roundToOne($progress/$total*100)
          : 0;

        $reviewPercent = ($review != 0)
          ? $this->roundToOne($review/$total*100)
          : 0;

        $nonePercent = ($none != 0)
          ? $this->roundToOne($none/$total*100)
          : 0;

        return [
          $validatedPercent,
          $progressPercent,
          $reviewPercent,
          $nonePercent
        ];
    }

    private function roundToOne($number)
    {
        return ($number > 0 && $number < 1)
          ? 1
          : $number;
    }
}
