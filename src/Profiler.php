<?php
namespace Src;
use \Src\Middleware\Helper as Plugin_Core;

class Profiler extends Plugin_Core {

	protected float $script_start_time;
	protected float $script_stop_time;
	protected float $script_exec_time;

	public function ram_usage() {
		// Returns the number of bytes used by the script when the function is called.
		return memory_get_usage();
	}

	public function ram_peak_usage() {
		// Returns the maximum amount of memory in bytes used 
		// by the script until the function is called.
		return memory_get_peak_usage();
	}

	public function start_timer() {
		return $this->script_start_time = microtime(true);
	}

	public function stop_timer() {
		return $this->script_stop_time = microtime(true);
	}

	public function timer() {
		$timer = ($this->script_stop_time - $this->script_start_time);
		return sprintf('%.2f', $timer);
	}

	public function get_sql() {
		// return exec("mysqladmin -u {$this->config->setting('db_user')} -p{$this->config->setting('db_pass')} -i 1 processlist;");
		$query = "SHOW FULL PROCESSLIST";
		$sql = $this->db->prepare($query);
		$sql->execute();

		foreach ($sql as $row){
		    printf("%s %s %s %s %s %s\n", $row["Id"], $row["Host"], $row["db"],
		        $row["Command"], $row["Time"], $row["State"]);
		}
	}

}