<?php

use Carbon\Carbon;

if(!function_exists('formatDate')){
    /**
     * Formats a given timestamp into a date string.
     *
     * @param string $timestamp A timestamp to be formatted.
     * @return string A formatted date string in Y-m-d format.
     */
    function formatDate($timestamp){
        // return date('Y-m-d', strtotime($timestamp));
        return Carbon::parse($timestamp)->format('Y-m-d');
    }
}