<?php 

namespace BlueFission\System;

/**
 * Class System
 * This class is used to run system commands.
 */
class System {
	
	/**
	 * @var string $_response The output of the command
	 */
	protected $_response;
	/**
	 * @var string $_status
	 */
	protected $_status;
	/**
	 * @var Process $_process The process information of the command
	 */
	protected $_processes = [];
	/**
	 * @var int $_timeout The maximum execution time of the command in seconds
	 */
	protected $_timeout = 60;
	/**
	 * @var string $_cwd The current working directory of the command
	 */
	protected $_cwd;
	/**
	 * @var string $_output_file The file to write the command output to
	 */
	protected $_output_file;

	protected $_read_streams;

	/**
	 * Initialize the class
	 */
	public function __construct() {}

	/**
	 * Check if the command is valid before running it
	 *
	 * @param string $command
	 * @return boolean
	 */
	public function isValidCommand($command) {
	    $returnVal = exec($command . ' 2>&1', $output, $returnVal);
	    return !$returnVal;
	}

	/**
	* Execute a command
	*
	* @param string $command  The command to execute
	* @param boolean $background  Execute command in background
	* @param array $options Additional options for the command
	*
	* @throws \InvalidArgumentException when $command is empty or not a string
	*/
	public function run( $command, $background = false, $options = array() ) {
		if (!$command)
			throw( new \InvalidArgumentException("Command cannot be empty!") );

		// if(!$this->isValidCommand($command))
		// 	throw( new \InvalidArgumentException("Invalid command!") );

		if (!empty($options)) {
			foreach ($options as $opt) {
				$command .= ' ' . escapeshellarg($opt);
			}
		}

		$this->_command = $command;

		$descriptorspec = [
			0 => ["pipe", "r"],
			1 => ["pipe", "w"],
			2 => ["pipe", "w"]
		];

		if (isset($this->_output_file)) {
			$descriptorspec[1] = ["file", $this->_output_file, "a"];
		}

		$options = [
			'timeout' => $this->_timeout,
			'cwd' => $this->_cwd
		];

		$process = new Process($command, $this->_cwd, null, $descriptorspec, $options);
		$this->_process[] = $process;
		end($this->_process)->start();

		$this->_response = end($this->_process)->output();

		return $process;
	}

	public function start( $command, $options = []) {
        if (!$command)
            throw( new \InvalidArgumentException("Command cannot be empty!") );

        // if(!$this->isValidCommand($command))
        //     throw( new \InvalidArgumentException("Invalid command!") );

        if (!empty($options)) {
            foreach ($options as $opt) {
                $command .= ' ' . escapeshellarg($opt);
            }
        }

        $this->_command = $command;

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        if (isset($this->_output_file)) {
            $descriptorspec[1] = ["file", $this->_output_file, "a"];
        }

        $options = [
            'timeout' => $this->_timeout,
            'cwd' => $this->_cwd
        ];

        $process = new Process($command, $this->_cwd, null, $descriptorspec, $options);
        $processId = uniqid('process_');
    	$this->_processes[$processId] = $process;
        end($this->_processes)->start();

        // Make the streams non-blocking
        // stream_set_blocking(end($this->_processes)->pipes(1), false);
        // stream_set_blocking(end($this->_processes)->pipes(2), false);

        // Add the streams to _read_streams
        $this->_read_streams[] = end($this->_processes)->pipes(1);
        $this->_read_streams[] = end($this->_processes)->pipes(2);

        $this->_response = '';

        return $processId;
    }

    public function stop($processId) {
    	if (isset($this->_processes[$processId])) {
    		$this->_processes[$processId]->stop();
    	}
    }

    public function readAvailableOutput($processId) {
	    if (isset($this->_processes[$processId])) {
	        $read_streams = [$this->_processes[$processId]->pipes(1), $this->_processes[$processId]->pipes(2)];
	        $write_streams = null;
	        $except_streams = null;
	        $output = '';

	        while (stream_select($read_streams, $write_streams, $except_streams, 0, 200000) > 0) {
	            foreach ($read_streams as $stream) {
	                $output .= stream_get_contents($stream);
	            }
	        }

	        return $output;
	    }
	    return false;
	}


	public function writeInput($processId, $input) {
	    if (isset($this->_processes[$processId])) {
	        $process = $this->_processes[$processId];//['process'];
	        fwrite($process->pipes(0), $input);
	    }
	}

	public function readOutput($processId) {
	    if (isset($this->_processes[$processId])) {
	        $process = $this->_processes[$processId];//['process'];
	        return stream_get_contents($process->pipes(1));
	    }
	    return false;
	}

	public function isOutputAvailable(int $processId): bool
	{
	    $process = $this->processes[$processId] ?? null;

	    if (!$process) {
	        return false;
	    }

	    $status = stream_get_meta_data($process->pipes(1));
	    return !$status['eof'];
	}

	/**
	 * Get the process information of the command
	 *
	 * @return Process
	 */
	public function process() {
		return array_pop( $this->_processes );
	}

	/**
	* Get the command that was run
	*
	* @return string
	*/
	public function getCommand()
	{
		return $this->_command;
	}

	/**
	* Set or get the working directory
	*
	* @param string $cwd
	*/
	public function cwd($cwd)
	{
		if ( $cwd ) {
	    	$this->_cwd = $cwd;
		}

		return $this->_cwd;
	}

	/**
	* Get or set the timeout
	*
	* @return int
	*/
	public function timeout($timeout)
	{
		if ( $timeout ) {
			$this->_timeout = $timeout;
		}

		return $this->_timeout;
	}

	/**
	* Set or get the output file
	*
	* @param string $output_file
	*/
	public function outputFile($output_file)
	{
		if ( $output_file ) {
			$this->_output_file = $output_file;
		}
		return $this->_output_file;
	}

	/**
	* Get the response of the command
	*
	* @return mixed string|boolean response of the command or false if command was run in background
	*/
	public function response() {
		if ($this->_response) {
			return $this->_response;
		}
		return false;
	}

	/**
	* Get the message
	*
	* @return string message
	*/
	public function status() {
		return $this->_status;
	}
}