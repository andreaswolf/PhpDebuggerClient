<?php
namespace AndreasWolf\DebuggerClient\Protocol\Breakpoint;
/**
 * A breakpoint for a specific line.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class LineBreakpoint extends BaseBreakpoint {

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var int
	 */
	protected $line;

	/**
	 * @param string $file
	 * @param int $line
	 */
	public function __construct($file, $line) {
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return int
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * Checks if the breakpoint points to the given location. This might be difficult to get, e.g. when the breakpoint
	 * is only based on a line.
	 *
	 * @param string $file
	 * @param int $line
	 * @return boolean
	 */
	public function matchesPosition($file, $line) {
		return $file == $this->file && $line == $this->line;
	}


}
