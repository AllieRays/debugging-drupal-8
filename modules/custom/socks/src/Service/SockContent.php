<?php

namespace Drupal\socks\Service;
use Drupal\Core\Controller\ControllerBase;

class SockContent extends ControllerBase{

  public function displaySomethings(){
    $randomStrings = (Array(
      'I love all the Socks. ',
      'Really Sick of hearing about Socks at this point. ',
      'Can not get enough socks in my life. ',
     ));
    return ($randomStrings[array_rand($randomStrings)]);
  }
}

