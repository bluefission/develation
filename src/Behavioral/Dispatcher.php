<?php
namespace BlueFission\Behavioral;

use BlueFission\DevObject;
use BlueFission\DevValue;
use BlueFission\Behavioral\Behaviors\Behavior;
use BlueFission\Behavioral\Behaviors\Event;
use BlueFission\Behavioral\Behaviors\State;
use BlueFission\Behavioral\Behaviors\Action;
use BlueFission\Behavioral\Behaviors\Handler;
use BlueFission\Behavioral\Behaviors\HandlerCollection;
use BlueFission\Behavioral\Behaviors\BehaviorCollection;
use InvalidArgumentException;

class Dispatcher extends DevObject {
	protected $_behaviors;
	protected $_handlers;
	
	public function __construct( HandlerCollection $handlers = null ) {
		parent::__construct();
		$this->_behaviors = new BehaviorCollection();

		if ($handlers)
			$this->_handlers = $handlers;
		else
			$this->_handlers = new HandlerCollection();

		$this->init();
		$this->trigger(Event::LOAD);
	}

	public function __destruct() {
		if ( $this->_behaviors ) {
			$this->trigger(Event::UNLOAD);
		} else {
			// echo "\n\n".get_class($this). " has no behaviors\n\n";
		}
	}

	public function behavior( $behavior, $callback = null ) {
		if ( is_string($behavior) && DevValue::isNotEmpty($behavior))
			$behavior = new Behavior($behavior);

		if ( !($behavior instanceof Behavior) ) {
			throw new InvalidArgumentException("Invalid Behavior Type");
		}
			
		$this->_behaviors->add( $behavior );

		if ( $callback ) {
			try {
				$this->handler( new Handler( $behavior, $callback ) );
			} catch ( InvalidArgumentException $e ) {
				error_log( $e->getMessage() );
			}
		}
	}

	public function handler($handler) {
		if ($this->_behaviors->has($handler->name())) {
			$this->_handlers->add($handler);
		}
	}

	public function dispatch( $behavior, $args = null ) {
		if (is_string($behavior))
			$behavior = new Behavior($behavior);

		$this->trigger( $behavior, array($args) );
	}

	protected function trigger($behavior, $args = null) {
		$this->_handlers->raise($behavior, $this, $args);
	}

	protected function init() {
		$this->behavior(new Event(Event::LOAD));
		$this->behavior(new Event(Event::UNLOAD));
	}
}