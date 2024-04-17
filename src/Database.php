<?php
namespace Src;
use \PDO as PDO;

class Database extends PDO
{
	/**
	 * @var mixed
	 */
	protected $client_version;

	/**
	 * @var mixed
	 */
	protected $connection_status;

	/**
	 * @var mixed
	 */
	protected $server_version;

	/**
	 * @param $c
	 */
	public function __construct( $c )
	{
		//MySQL Connection
		if ( $c['config']->setting( 'db_type' ) == 'mysql' )
		{
			if ( $c['config']->setting( 'db_host' ) != '' )
			{
				parent::__construct( "mysql:host=" . $c['config']->setting( 'db_host' ) . ";port=" . $c['config']->setting( 'db_port' ) . ";dbname=" . $c['config']->setting( 'db_name' ) . "", $c['config']->setting( 'db_user' ), $c['config']->setting( 'db_pass' ) );
				PDO::setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
				PDO::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			else
			{
				exit( $c['config']->log->save( "There was a problem connecting to the databse", 'database.log' ) );
			}
		}
		elseif ( $c['config']->setting( 'db_type' ) == 'mssql' )
		{
			if ( $c['config']->setting( 'db_host' ) != '' )
			{
				$dsn = "sqlsrv:Server=" . $c['config']->setting( 'db_host' ) . "," . $c['config']->setting( 'db_port' ) . ";Database=" . $c['config']->setting( 'db_name' ) . "";
				parent::__construct( $dsn, $c['config']->setting( 'db_username' ), $c['config']->setting( 'db_pass' ) );
				PDO::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			else
			{
				exit( $c['config']->log->save( "There was a problem connecting to the databse", 'database.log' ) );
			}
		}
		elseif ( $c['config']->setting( 'db_type' ) == 'postgresql' )
		{
			if ( $c['config']->setting( 'db_host' ) != '' )
			{
				$dsn = "pgsql:Server=" . $c['config']->setting( 'db_host' ) . ";port=" . $c['config']->setting( 'db_port' ) . ";Database=" . $c['config']->setting( 'db_name' ) . "";
				parent::__construct( $dsn, $c['config']->setting( 'db_username' ), $c['config']->setting( 'db_pass' ) );
				PDO::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			else
			{
				exit( $c['config']->log->save( "There was a problem connecting to the databse", 'database.log' ) );
			}
		}
		else
		{
			// TODO: Set up ODBC connection
			if ( $c['config']->setting( 'db_host' ) != '' )
			{
				parent::__construct( "mysql:host=" . $c['config']->setting( 'db_host' ) . ";port=" . $c['config']->setting( 'db_port' ) . ";dbname=" . $c['config']->setting( 'db_name' ) . "", $c['config']->setting( 'db_user' ), $c['config']->setting( 'db_pass' ) );
				PDO::setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
				PDO::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			else
			{
				exit( $c['config']->log->save( "There was a problem connecting to the databse", 'database.log' ) );
			}
		}

	}

	/**
	 * @param $c
	 */
	public function sql_info( $c )
	{
		if ( $c['config']->setting( 'db_host' ) != '' )
		{
			$conn = new PDO( "mysql:host=" . $c['config']->setting( 'db_host' ) . ";dbname=" . $c['config']->setting( 'db_name' ) . "", $c['config']->setting( 'db_user' ), $c['config']->setting( 'db_pass' ) );

			$attributes = [
				"CLIENT_VERSION",
				"CONNECTION_STATUS",
				"SERVER_VERSION",
			];

			$data = [];

			foreach ( $attributes as $val )
			{
				$data[] = $conn->getAttribute( constant( "PDO::ATTR_$val" ) );
			}

			return $data;
		}
	}
}
