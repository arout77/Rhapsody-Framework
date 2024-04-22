<?php
namespace Src;

class Error extends Template
{
	// Description of above error type; a configuration, environment, etc issue?
	/**
	 * @var mixed
	 */
	public $category;

	// Debug help message in 'How to fix' section
	/**
	 * @var mixed
	 */
	public $message;

	// The specific setting or condition that caused failure
	/**
	 * @var string
	 */
	public $object = '';

	// The name of class where failure occurred
	/**
	 * @var mixed
	 */
	public $triggeredBy;

	// What kind of error occurred? Enum, General exception, etc
	/**
	 * @var mixed
	 */
	public $type;

	// For enums, the valid cases that are allowed.
	/**
	 * @var mixed
	 */
	public $valid_options;

	// The value of the obj/var that is invalid
	/**
	 * @var mixed
	 */
	public $value;

	public function display()
	{
		$this->render( 'error/exceptions.html.twig', [
			'message'       => self::getMessage(),
			'object'        => $this->object,
			'category'      => $this->category,
			'triggeredBy'   => $this->triggeredBy,
			'value'         => $this->value,
			'valid_options' => $this->valid_options,
		] );
	}

	/**
	 * @param $message
	 * @return mixed
	 */
	public function getMessage()
	{
		$message = $this->message;

		if ( $this->type == 'Enum' )
		{
			$message = <<<EOT
            The configuration file contains the following invalid setting:
            > $this->object = "$this->value"

            Open the .env file and locate the '$this->object' setting. Assign it
            one of the following valid options:
            EOT;
		}

		// if ( $this->type == 'Validation' )
		// {
		// 	$message = <<<EOT
		//     You are trying to use a validation rule that does not exist:
		//     $this->object
		//     on the form input field name '$this->value'

		//     To fix this error, go back to your controller where you set the valdiation rules, and assign it
		//     one of the following valid rules:
		//     EOT;
		// }

		return $message;
	}
}