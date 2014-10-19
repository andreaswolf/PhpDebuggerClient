<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use React\Promise;


/**
 * A command that can "promise" to do something when its result arrives – the execution of code is deferred until
 * we know about success/failure of the command, hence the name.
 *
 * Use ``$command->promise()->then($successCallback, $failureCallback, $progressCallback)`` to attach callbacks.
 *
 * Currently, using the promise mechanism is optional. If we switch to it as the only mechanism to execute code when
 * a command result is processed, this class can be merged into the base command class.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class Deferrable extends DebuggerBaseCommand implements Promise\PromisorInterface {

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

	/**
	 * @return void
	 */
	protected function reject() {
		if ($this->promise && is_callable($this->rejectCallback)) {
			call_user_func($this->rejectCallback);
		}
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function resolve($value) {
		if ($this->promise && is_callable($this->resolveCallback)) {
			call_user_func($this->resolveCallback, $value);
		}
	}

}
