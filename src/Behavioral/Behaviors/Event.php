<?php
namespace BlueFission\Behavioral\Behaviors;

/**
 * Class Event
 * 
 * Represents a behavioral Event in the BlueFission Behavioral system.
 */
class Event extends Behavior
{
	const LOAD = 'OnLoad';
	const UNLOAD = 'OnUnload';
	const ACTIVATED = 'OnActivated';
	const CHANGE = 'OnChange';
	const COMPLETE = 'OnComplete';
	const SUCCESS = 'OnSuccess';
	const FAILURE = 'OnFailure';
	const MESSAGE = 'OnMessageUpdate';

	/**
	 * Constructor for the Event class
	 *
	 * @param string $name The name of the event.
	 */
	public function __construct( $name )
	{
		parent::__construct( $name, 0, true, false );
	}
}
