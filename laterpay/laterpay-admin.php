<?php

if ( class_exists( 'LaterPay_Admin_Controller' ) ) {
    $LaterPay_Admin_Controller = new LaterPay_Controller_Admin();
    $LaterPay_Admin_Controller->run();
}
