<?php

if (!function_exists('exception_array')) {
  function exception_array(\Exception $exception) {
    $return = [
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
      'message' => $exception->getMessage(),
    ];
    
    return $return;
  }
}

if (!function_exists('exception_array_all')) {
  function exception_array_all(\Exception $exception) {
    $return = [exception_array($exception)];
    
    while ($previous = $exception->getPrevious()) {
      $return[] = exception_array($previous);
    }
    
    return $return;
  }
}