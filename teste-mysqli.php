<?php
header("Content-Type: text/plain; charset=UTF-8");

echo "mysqli carregado? ";
var_dump(extension_loaded("mysqli"));

echo "classe mysqli existe? ";
var_dump(class_exists("mysqli"));
?>