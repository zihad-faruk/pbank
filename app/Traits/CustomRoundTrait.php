<?php

namespace App\Traits;
trait CustomRoundTrait
{
    /**
     * Round the number according to custom rule.
     *
     * @param float $number
     * @param string $mode
     * @return float
     */
    public function customRound(
        float $number,
        string $mode = 'fraction'
    ): float {
        //For Special Cases make the whole number round e.x JPY
        if ($mode == "whole") {
            return ceil($number);
        }
        //If number=0, return the rounded number
        if (empty($number)) {
            return $number;
        }

        $decimal_array = explode('.', $number);
        $whole_part = $decimal_array[0] ?? 0;
        $fractional_part = $decimal_array[1] ?? 0;

        if (isset($fractional_part) && ! empty($fractional_part)) {
            $result = $this->calculateFractionDigits($whole_part, $fractional_part);
        } else {
            $result = $whole_part;
        }
        return $result;
    }

    /**
     * Round the number according to custom rule and format it to 2 decimal point.
     *
     * @param float $number
     * @param string $mode
     * @return string
     */
    public function customRoundAndFormatNumber(
        float $number,
        string $mode = 'fraction'
    ): string {
        return number_format($this->customRound($number, $mode), 2);
    }

    /**
     * The custom logic to calculate fractional part.
     *
     * @param float $whole_part
     * @param string $fractional_part
     * @return float
     */
    public function calculateFractionDigits(
        float $whole_part,
        string $fractional_part
    ): float {
        $fraction_data = str_split($fractional_part);
        $first_digit = $fraction_data[0] ?? 0;
        $second_digit = $fraction_data[1] ?? 0;
        $third_digit = $fraction_data[2] ?? 0;
        $carry = 0;
        if (isset($third_digit) && ! empty($third_digit)) {
            $carry++;
        }
        if (isset($second_digit) && ! empty($second_digit)) {
            if ($carry != 0) {
                $second_digit++;
                if ($second_digit > 9) {
                    $second_digit = 0;
                }
            }
            $carry++;
        }
        if (isset($first_digit) && ! empty($first_digit)) {
            if ($carry != 0) {
                $first_digit++;
            }
            if ($first_digit > 9) {
                $first_digit = 0;
                $whole_part++;
            }
        }
        return (float)$whole_part . '.' . $first_digit . $second_digit;
    }
}