<?php

  class localisation {
    function init($config) {
      if (strtolower($config["language"]) != "en") {
        if (file_exists("inc/ln/" . strtolower($config["language"]) . ".json")) {
          $this->translations = json_decode(file_get_contents("inc/ln/" . strtolower($config["language"]) . ".json"), true);
        } elseif (file_exists("../inc/ln/" . strtolower($config["language"]) . ".json")) {
          $this->translations = json_decode(file_get_contents("../inc/ln/" . strtolower($config["language"]) . ".json"), true);
        }
      }
    }

    function _($translatestring) {
      if (isset($this->translations) && in_array($translatestring, array_keys($this->translations))) {
        return $this->translations[$translatestring];
      } else {
        return $translatestring;
      }
    }
  }


?>
