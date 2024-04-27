<?php
namespace Src\Middleware;

class Format
{
	/**
	 * @param $string
	 * @return mixed
	 */
	public function age( $string ): int
	{
		try {
			### Convert birth date to age (in years) ###
			$from = new \DateTimeImmutable( $string );
			$to   = new \DateTimeImmutable( 'today' );
			return $from->diff( $to )->y;
		}
		catch ( Exception $e )
		{
			// $e->getLine();
		}
	}

	/**
	 * @param $string
	 */
	public function date( $string )
	{
		// Example output: 11/27/2013
		return gmdate( "n/d/Y", $string );
	}

	/**
	 * @param $date
	 */
	public function date_to_timestamp( $date )
	{
		// Convert given month, day and year to a Unix timestamp
		return strtotime( $date );
	}

	/**
	 * @param $string
	 */
	public function datereverse( $string )
	{
		// Example output: 2013/19/03
		return gmdate( "Y/d/n", $string );
	}

	/**
	 * @param $string
	 */
	public function datetime( $string )
	{
		// Example output: 11/27/2013 8:08am
		return gmdate( "n/d/Y  g:ia", $string );
	}

	/**
	 * @param $string
	 */
	public function datewords( $string )
	{
		// Example output: Monday, March 8, 2003
		return gmdate( "l, F d, Y", $string );
	}

	/**
	 * @param $string
	 */
	public function datewords_no_prefix( $string )
	{
		// Example output: March 8, 2003
		return gmdate( "F d, Y", $string );
	}

	/**
	 * @param $number
	 */
	public function int_format( $number )
	{
		return number_format( $number );
	}

	/**
	 * @param $phoneNumber
	 * @return mixed
	 */
	public function phone( $phoneNumber ): string
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
	public function time( $string )
	{
		// Example output:  7:17am
		return gmdate( "g:ia", $string );
	}
}