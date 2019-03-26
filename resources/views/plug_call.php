<?php

//echo "<br />Hello2";
//echo base_path();

require base_path() . "/App/Plug/" . $frame->plug_name . "/" . $frame->plug_name . ".php";
$class = $frame->plug_name;

$plug = new $class;

echo $plug->init();

