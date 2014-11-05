<?php

class LaterPay_Helper_Passes {

     /**
     * Default time range. Used while passes creation.
     *
     * @var string
     */
    const default_time_range = 'Day';
    
    /**
     * @var array
     */
    public static $time_ranges = array(
        'Hour',
        'Day',
        'Week',
        'Month',
        'Year'
    );    
    
    /**
     * @var array
     */
    public static $coverages = array(
        'All content',
        'All content except for',
        'All content in category',
    );
    
    /**
     * @var array
     */
    public static $coverage_detail = array(
        'First category',
    );
    
}
