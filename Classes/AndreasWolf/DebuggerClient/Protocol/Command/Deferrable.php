<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use React\Promise;


/**
 * A command that
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class Deferrable extends DebuggerBaseCommand {

	/**
	 * @var Promise\PromiseInterface
	 */
	protected $promise;

	/**
	 * @var callable
	 */
	protected $resolveCallback;
	/**
	 * @var callable
	 */
	protected $rejectCallback;
	/**
	 * @var callable
	 */
	protected $progressCallback;


	public function promise() {
		if (NULL === $this->promise) {
			$this->promise = new Promise\Promise(function ($resolve, $reject, $progress) {
				$this->resolveCallback = $resolve;
				$this->rejectCallback = $reject;
				$this->progressCallback = $progress;
			});
		}

		return $this->promise;
	}

}
