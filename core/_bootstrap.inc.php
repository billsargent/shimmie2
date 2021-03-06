<?php
/*
 * Load all the files into memory, sanitise the environment, but don't
 * actually do anything as far as the app is concerned
 */

global $config, $database, $user, $page;

require_once "core/sys_config.inc.php";
require_once "core/util.inc.php";
require_once "lib/context.php";
require_once "vendor/autoload.php";
require_once "core/imageboard.pack.php";

// set up and purify the environment
_version_check();
_sanitise_environment();

// load base files
ctx_log_start("Opening files");
$_shm_files = array_merge(
	zglob("core/*.php"),
	zglob("ext/{".ENABLED_EXTS."}/main.php")
);
foreach($_shm_files as $_shm_filename) {
	if(basename($_shm_filename)[0] != "_") {
		require_once $_shm_filename;
	}
}
unset($_shm_files);
unset($_shm_filename);
ctx_log_endok();

// connect to the database
ctx_log_start("Connecting to DB");
$database = new Database();
$config = new DatabaseConfig($database);
ctx_log_endok();

// load the theme parts
ctx_log_start("Loading themelets");
foreach(_get_themelet_files(get_theme()) as $themelet) {
	require_once $themelet;
}
unset($themelet);
$page = class_exists("CustomPage") ? new CustomPage() : new Page();
ctx_log_endok();

// hook up event handlers
_load_event_listeners();
send_event(new InitExtEvent());
