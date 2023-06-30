<?php

if (!function_exists('currencyFormat')) {
    function currencyFormat($debit,$currency,$decimal=0)
    {
        $formattedDebit = number_format($debit, $decimal, ',', '.');
        return $currency . ' ' . $formattedDebit;
    }
}