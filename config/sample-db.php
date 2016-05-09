<?php
// The main db.php is added to .gitignore
// Add your own db.php in this directory
// Use this as a template.
// As an alternate you can use environment vars.
// I include this below
$config['db']['host']   = (getenv('db_host')?:'hostname');
$config['db']['user']   = (getenv('db_user')?:'username');
$config['db']['pass']   = (getenv('db_pass')?:'p455w0rd');
$config['db']['dbname'] = (getenv('db_dbname')?:'dbname');
