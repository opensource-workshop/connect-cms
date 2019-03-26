<?php

//echo "<br />Hello2";
//echo base_path();

//require base_path() . "/App/Plug/" . $frame->plug_name . "/" . $frame->plug_name . ".php";
//$class = $frame->plug_name;

//$plug = new $class;

//echo $plug->init();

echo $action_plugin;
$action = "init";

	$class_name = "app\ManagePlugins\\" . ucfirst($action_plugin) . "Manager\\" . ucfirst($action_plugin) . "Manager";
	$managePlugin = new $class_name;
	$managePlugin->$action($request);


