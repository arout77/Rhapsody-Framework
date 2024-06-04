<?php
namespace Src\Middleware;

use Src\Kernel;

class Validation extends Kernel
{
	/**
	 * @var array
	 */
	public $errors = [];

	/**
	 * @var array
	 *
	 * Rules below that contain blank error messages have the
	 * messages defined within the methods
	 */
	public $message = [
		'alpha'      => 'Must contain letters only',
		'alphanum'   => 'Must contain only letters and numbers',
		'date'       => 'Must be a valid date',
		'datetime'   => 'Must be a valid date and time',
		'digits'     => 'Must contain numbers only',
		'email'      => 'Must be a valid email',
		'float'      => 'Must be a float (decimal) value',
		'identical'  => '',
		'ipv4'       => 'Must be a valid IPV4 address',
		'ipv6'       => 'Must be a valid IPV6 address',
		'min'        => '',
		'min_length' => '',
		'max'        => '',
		'max_length' => '',
		'required'   => 'Required field',
		'string'     => 'Must be a string',
		'url'        => 'Must be a valid URL',
		'word'       => 'Must contain only letters, whole numbers, spaces, dashes or underscores',
	];

	/**
	 * @param $string
	 */
	public function alpha( $string ): bool
	{
		return ctype_alpha( $string );
	}

	/**
	 * @param $string
	 */
	public function alphanum( $string ): bool
	{
		return ctype_alnum( $string );
	}

	/**
	 * @param $date
	 * @param $format
	 * @return mixed
	 */
	public function date( $date, $format = 'Y-m-d' ): bool
	{
		try {
			$dateTime = date_parse( $date );
			return $dateTime["warning_count"] == 0 and $dateTime["error_count"] == 0;
		}
		catch ( Exception $e )
		{
			$this->log->save( $e->getMessage() );
		}

		return false;
	}

	public function digits(): bool
	{
		return ctype_digit( $string );
	}

	/**
	 * @param $string
	 */
	public function email( $string ): bool
	{
		if ( filter_var( $email_a, FILTER_VALIDATE_EMAIL ) != false )
		{
			return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function errors()
	{
		// Prints any error messages to screen
		if ( !empty( $this->errors ) )
		{
			foreach ( $this->errors as $e )
			{
				$errors['input'] =
					[
					'field'    => $e['field'],
					'value'    => $e['value'],
					'rule'     => $e['rule'],
					'messages' => $e['messages'],
				];
			}

			// array_multisort( $errors );
			return $errors;
		}

		return null;
	}

	public function float(): bool
	{
	}

	/**
	 * @param array $input
	 * @param array $rules
	 */
	public function form( Array $input, Array $rules )
	{
		// Validate form inputs
		return self::rules( $input, $rules );
	}

	/**
	 * @param $base_value
	 * @param $comp_value
	 */
	public function identical( $base_value, $comp_value ): bool
	{
		if ( $base_value !== $comp_value )
		{
			$this->message['identical'] = "{$base_value} must be identical to: {$comp_value}";
			return false;
		}

		return true;
	}

	/**
	 * @param $ip
	 */
	public function ip( $ip ): bool
	{
		// Check if a valid IP address
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) != false )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $ip
	 */
	public function ipv4( $ip ): bool
	{
		// Check for IPV4 specifically
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) != false )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $ip
	 */
	public function ipv6( $ip ): bool
	{
		// Check for IPV6 specifically
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) != false )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $string
	 * @param $max_allowed
	 */
	public function max( $int, $max_allowed ): bool
	{
		if ( $int > $max_allowed )
		{
			$this->message['max'] = "Cannot be greater than {$max_allowed}";
			return false;
		}
		return true;
	}

	/**
	 * @param $string
	 */
	public function max_length( $string, $max_len_allowed, $encoding = null ): bool
	{
		$length = mb_strlen( $string, $encoding );

		if ( $length > $max_len_allowed )
		{
			$this->message['max_length'] = "Cannot contain more than {$max_len_allowed} characters";
			return false;
		}
		return true;
	}

	/**
	 * @param $string
	 * @param $min_allowed
	 */
	public function min( $int, $min_allowed ): bool
	{
		if ( $int < $min_allowed )
		{
			$this->message['min'] = "Cannot be less than {$min_allowed}";
			return false;
		}
		return true;
	}

	/**
	 * @param $string
	 */
	public function min_length( $string, $min_len_allowed, $encoding = null ): bool
	{
		$length = mb_strlen( $string, $encoding );

		if ( $length < $min_len_allowed )
		{
			$this->message['min_length'] = "Cannot be less than {$min_len_allowed} characters";
			return false;
		}
		return true;
	}

	/**
	 * @param $rule
	 * @return mixed
	 */
	public function parseRule( $rule )
	{
		$meth       = [];
		$methodName = '';
		$validate   = explode( "|", $rule );

		foreach ( $validate as $method )
		{
			if ( str_contains( $method, "," ) )
			{
				// Grab the text preceding the comma
				$methodName = strtolower( strstr( $method, ',', true ) );
				if ( $methodName != 'date' )
				{
					// Get the numerical value for this rule
					$num_value = preg_replace( "/[^0-9]/", '', $method );

					$meth['ruleName']   = $methodName;
					$meth['ruleParams'] = $num_value;

					return $meth;
				}

				if ( $methodName == 'date' )
				{
					// Get the numerical value for this rule
					$dateFormat = strstr( $method, ',' );
					$dateFormat = substr( $dateFormat, 1 );

					$meth['ruleName']   = $methodName;
					$meth['ruleParams'] = $dateFormat ?? '';

					return $meth;
				}

			}
			elseif ( str_contains( $method, "=" ) )
			{
				$methodName = strtolower( strstr( $method, '=', true ) );

				if ( $methodName == 'identical' )
				{
					// Get the value
					$mustbethesameasthis = strstr( $method, '=' );
					$mustbethesameasthis = substr( $mustbethesameasthis, 1 );

					$meth['ruleName']   = $methodName;
					$meth['ruleParams'] = $mustbethesameasthis;

					return $meth;
				}
			}
			else
			{
				$methodName         = strtolower( $method );
				$meth['ruleName']   = $methodName;
				$meth['ruleParams'] = '';

				return $meth;
			}
		}
	}

	/**
	 * @param $phoneNumber
	 * @return mixed
	 */
	public function phone( $phoneNumber )
	{
		// Strip all non-integers from input
		$phoneNumber = preg_replace( '/[^0-9]/', '', $phoneNumber );

		if ( strlen( $phoneNumber ) > 10 )
		{
			// Prepend country code to phone number
			$countryCode = substr( $phoneNumber, 0, strlen( $phoneNumber ) - 10 );
			$areaCode    = substr( $phoneNumber, -10, 3 );
			$nextThree   = substr( $phoneNumber, -7, 3 );
			$lastFour    = substr( $phoneNumber, -4, 4 );

			$phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
		}
		else if ( strlen( $phoneNumber ) == 10 )
		{
			// 10 digit phone number (Area code + number)
			$areaCode  = substr( $phoneNumber, 0, 3 );
			$nextThree = substr( $phoneNumber, 3, 3 );
			$lastFour  = substr( $phoneNumber, 6, 4 );

			$phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
		}
		else if ( strlen( $phoneNumber ) == 7 )
		{
			// 7 digit number
			$nextThree = substr( $phoneNumber, 0, 3 );
			$lastFour  = substr( $phoneNumber, 3, 4 );

			$phoneNumber = $nextThree . '-' . $lastFour;
		}

		return $phoneNumber;
	}

	/**
	 * @param $string
	 */
	public function required( $string )
	{
		if ( !empty( $string ) && $string != '' )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $string
	 * @param $rules
	 */
	public function rules( $string, $rules )
	{
		// Usually $_POST, but can be any array
		if ( is_array( $string ) )
		{
			// Grab the array keys and values
			foreach ( $string as $field => $value )
			{
				// Get the rules for each input
				foreach ( $rules as $key => $rule )
				{
					// Match the form input name with index name in rules array
					// Ex.  if( ['username'] from $_POST equals $rules['username'])
					if ( $field == $key )
					{
						foreach ( $rule as $label => $rule )
						{
							// Get the rule(s) for this input field
							$validate = explode( "|", $rule );

							foreach ( $validate as $method )
							{
								$method = self::parseRule( $method );

								if ( !method_exists( Validation::class, $method['ruleName'] ) && $method['ruleName'] != false )
								{
									self::setErrorMessage( $rule, $field, $method['ruleName'] );
								}

								// If the rule does not have a comma separated value
								// Ex. 'min_chars,3'
								// Or equal sign separaated value
								// Ex. 'identical="something"'
								if (
									$method['ruleName'] != 'date' &&
									$method['ruleName'] != 'min' &&
									$method['ruleName'] != 'min_length' &&
									$method['ruleName'] != 'max' &&
									$method['ruleName'] != 'max_allowed'
								)
								{
									if ( !self::{$method['ruleName']}( $value, $method['ruleParams'] ) )
									{
										$this->errors[$field] = [
											'field'    => $field,
											'value'    => $value,
											'rule'     => $method['ruleName'],
											'messages' => $this->message[$method['ruleName']],
										];
									}
								}
								// Rule containing a numerical option
								// Ex: 	'max_chars, 10'

								elseif (
									$method['ruleName'] == 'min' ||
									$method['ruleName'] == 'min_length' ||
									$method['ruleName'] == 'max' ||
									$method['ruleName'] == 'max_allowed'
								)
								{
									// Validation check failed for this rule
									if ( !self::{$method['ruleName']}( $value, $method['ruleParams'] ) )
									{
										$this->errors[$field] = [
											'field'    => $field,
											'value'    => $value,
											'rule'     => $method["ruleName"],
											'messages' => $this->message[$method['ruleName']],
										];
									}
								}
								elseif ( $method['ruleName'] == 'identical' )
								{
									// Rule to ensure given values are identical
									// Ex: 	'identical="$_POST['password']"'

									// Get the comparison value for this rule
									$comp_value = $method['ruleParams'];
									// If the passed array key ($_POST, $_GET, array()) matches the array key in the rules array
									// Get the value from post/get/array and assign as the base value
									$base_value = null;
									if ( $field == $key )
									{
										$base_value = $value;
									}

									// Validation check failed for this rule
									if ( !self::{$method['ruleName']}( $base_value, $comp_value ) )
									{
										$this->errors[$field] = [
											'field'    => $field,
											'value'    => $value,
											'rule'     => $method["ruleName"],
											'messages' => $this->message[$method['ruleName']],
										];
									}
								}
								elseif ( $method['ruleName'] == 'date' )
								{
									// Rule to ensure given value is a valid date. Accepts
									// an optional date format. Format defaults to "Y-m-d"
									// Format must be separated from rule with a comma, and
									// the value must be enclosed in double quotes
									// Ex: 	'date,"m-d-Y"'
									if ( !empty( $method['ruleParams'] ) && $method['ruleParams'] != '' )
									{
										if ( !self::date( $value, $method['ruleParams'] ) )
										{
											$this->errors[$field] = [

												'field'    => $field,
												'value'    => $value,
												'rule'     => 'date',
												'messages' => $this->message[$method['ruleName']],
											];

										}
									}
									else
									{
										if ( !self::date( $value ) )
										{
											$this->errors[$field] = [

												'field'    => $field,
												'value'    => $value,
												'rule'     => 'date',
												'messages' => $this->message[$method['ruleName']],
											];

										}
									}
								}
							}
						}

						if ( !empty( $this->errors ) )
						{
							$_SESSION['validation']           = $_POST;
							$_SESSION['validation']['errors'] = self::errors();
							// var_dump( $_SESSION['validation']['errors'] );exit;
							header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
							exit;
						}
						else
						{
							if ( isset( $_SESSION['validation'] ) )
							{
								unset( $_SESSION['validation']['errors'] );
								unset( $_SESSION['validation'] );
							}
						}
					}
					else
					{
						// No match found for input field name and the
						// name for the input in the rules array
						continue;
					}
				}
			}
		}
		else
		{
			// $string passed was not an array
		}

	}

	/**
	 * @param $rule
	 */
	public function setErrorMessage( $rules, $field, $method )
	{
		// Invalid rule; throw error
		// $passedrules = implode( ',', $rules );
		$message = <<<EOT
You passed the following rules on the form input field name '$field':
"{$rules}"
which contains a validation rule that does not exist:
$method

To fix this error, go back to your controller where you set the valdiation rules, and assign it
one of the following valid rules:
EOT;
		$valid_methods = get_class_methods( Validation::class );

		// Remove internal methods from list of available methods
		$valid_methods = array_diff( $valid_methods, ['validate_rule'] );
		$valid_methods = array_diff( $valid_methods, ['validate_value'] );
		$valid_methods = array_diff( $valid_methods, ['__construct'] );
		$valid_methods = array_diff( $valid_methods, ['throwError'] );
		$valid_methods = array_diff( $valid_methods, ['errors'] );
		$valid_methods = array_diff( $valid_methods, ['rules'] );
		$valid_methods = array_diff( $valid_methods, ['setErrorMessage'] );

		$params['type']          = "Validation";
		$params['category']      = "Exception";
		$params['triggeredBy']   = Validation::class;
		$params['object']        = $method;
		$params['value']         = $field;
		$params['valid_options'] = $valid_methods;
		return $this->throwError( $params, $message );
	}

	public function url(): bool
	{
		if ( filter_var( $url, FILTER_VALIDATE_URL ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $string
	 * @param $rule
	 * @param $value
	 */
	public function validate_value( $string, $rule, $value )
	{
		if ( method_exists( "$rule" ) )
		{
			return self::{$rule}( $string, $value );
		}
	}

	/**
	 * @param $string
	 */
	public function word( $string ): bool
	{
		// Allows alphanum, underscores, dashes and spaces
		if ( !preg_match( '/^[a-zA-Z0-9_- ]+$/', $string ) )
		{
			return false;
		}

		return true;
	}
}
