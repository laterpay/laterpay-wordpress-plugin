<?php

class helper {

    public static function kill_firefox() {

        system(" for i in `ps -A | grep firefox | awk ' {print $1}'`; do kill -9 \$i; done ");
    }

    public static function clean_folds() {

        system(" rm -fr reports/*; rm -fr snapshots/*; ");
    }

}

