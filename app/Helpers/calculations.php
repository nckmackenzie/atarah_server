<?php

if (!function_exists('calculateDiscount')) {
    function calculateDiscount($grossAmount, $discount, $discountType)
    {
        if ($discountType === 'amount') {
            // $totalAmount = max(0, $grossAmount - $discount); // Ensure the total amount isn't negative
            $totalAmount = max(0, $discount); 
        } elseif ($discountType === 'percentage') {
            $totalAmount = max(0, $grossAmount * ($discount / 100));
        } else {
            $totalAmount = 0;
        }

        return $totalAmount;
    }
}

if (!function_exists('calculateVAT')) {
    function calculateVAT($vatRate, $amount, $vatType)
    {
        switch ($vatType) {
            case 'no_vat':
                $exclusiveAmount = $amount;
                $vatAmount = 0;
                $inclusiveAmount = $amount;
                break;

            case 'inclusive':
                $exclusiveAmount = $amount / (1 + ($vatRate / 100));
                $vatAmount = $amount - $exclusiveAmount;
                $inclusiveAmount = $amount;
                break;

            case 'exclusive':
                $exclusiveAmount = $amount;
                $vatAmount = $amount * ($vatRate / 100);
                $inclusiveAmount = $exclusiveAmount + $vatAmount;
                break;

            default:
                throw new InvalidArgumentException("Invalid VAT type provided.");
        }

        return [
            'exclusive_amount' => $exclusiveAmount,
            'vat_amount' => $vatAmount,
            'inclusive_amount' => $inclusiveAmount,
        ];
    }
}

if(!function_exists('calculateWorkingDays')) {
    function calculateWorkingDays($startDate, $endDate) {
        $currentDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $totalWorkingDays = 0;
    
        while ($currentDate <= $endDate) {
            if (date('w', $currentDate) != 0) {
                $totalWorkingDays++;
            }
    
            $currentDate = strtotime("+1 day", $currentDate);
        }
    
        return $totalWorkingDays;
    }
}