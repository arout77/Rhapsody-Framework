<?php
namespace Src\Middleware;

use Src\Middleware\Helper;

class Geoip extends Helper
{
	/**
	 * @var array
	 */
	public $errors = [];

	/**
	 * Returns distance (as the crow flies) between two sets of lat & long points
	 * By default, returns value in miles. To return kilometers or nautical miles,
	 * pass "K" or "N" as the fifth parameter
	 * @param $lat1
	 * @param $lon1
	 * @param $lat2
	 * @param $lon2
	 * @param $unit
	 * @return mixed
	 */
	public function distance( float $lat1, float $lon1, float $lat2, float $lon2, string $unit = "M" ): int | float
	{
		$latitude1  = filter_var( $lat1, FILTER_SANITIZE_NUMBER_FLOAT );
		$longitude1 = filter_var( $lat1, FILTER_SANITIZE_NUMBER_FLOAT );
		$latitude2  = filter_var( $lat2, FILTER_SANITIZE_NUMBER_FLOAT );
		$longitude2 = filter_var( $lat2, FILTER_SANITIZE_NUMBER_FLOAT );
		if ( $latitude1 === false || $longitude1 === false || $latitude2 === false || $longitude2 === false )
		{
			return $this->errors[] = 'One or more given values are not a valid latitude or longitude';
		}

		$unit = strtoupper( $unit );

		$theta = $lon1 - $lon2;

		$dist =
		sin( deg2rad( $lat1 ) ) *
		sin( deg2rad( $lat2 ) ) +
		cos( deg2rad( $lat1 ) ) *
		cos( deg2rad( $lat2 ) ) *
		cos( deg2rad( $theta ) );

		$dist = acos( $dist );
		$dist = rad2deg( $dist );

		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper( $unit );

		if ( $unit == "K" )
		{
			return ( $miles * 1.609344 );
		}

		if ( $unit == "N" )
		{
			return ( $miles * 0.8684 );
		}

		$miles = number_format( $miles, 1, '.', '' );

		return $miles;
	}
}