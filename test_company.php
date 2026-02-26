<?php
require_once __DIR__ . '/mvc/models/CompanyModel.php';
$m = new CompanyModel();
$all = $m->getAll();
var_dump($all);
