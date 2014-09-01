<?php

namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class BasicHelper extends \Codeception\Module {

    public function doanloadLatestPlugin($path) {

        if (file_exists($path)) {

            $file = file_get_contents($path);

            file_put_contents('tests/_data/plugin.zip', $file);
        };
    }

    /*

      private $data = null;

      private function readFile($k) {

      $file = 'tests/_data/install_' . $k . '.dat';

      if (file_exists($file))
      $this->data[$k] = file_get_contents($file);
      }

      private function readXml() {

      $file = 'tests/_data/install.xml';

      if (file_exists($file)) {

      $string = file_get_contents($file);

      $array = (array) json_decode(json_encode(simplexml_load_string($string)), true);

      foreach ($array as $k => $v)
      $this->data[$k] = $v;

      return;
      };

      $this->data = array();
      }

      public function in($k = '') {

      if (!$this->data)
      $this->readXml();

      if (isset($this->data[$k]))
      return $this->data[$k];

      if (!isset($this->data[$k]))
      $this->readFile($k);

      if (isset($this->data[$k]))
      return $this->data[$k];

      return null;
      }
     */
}

