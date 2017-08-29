<?php if (!defined('FCPATH')) exit('No direct script access allowed');
return array (
  'socket_type' => 'tcp', //`tcp` or `unix`
  'socket' => '/var/run/redis.sock', // in case of `unix` socket type
  'host' => '127.0.0.1',
  'password' => NULL,
  'port' => 6379,
  'timeout' => 0,
);