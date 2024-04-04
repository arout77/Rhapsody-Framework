<?php 
namespace Validate\Enums;

enum Database_Types: string 
{
    // mysql, mssql, postgresql or ODBC (select mysql if you're using MariaDB)
    case mysql = "mysql";
    case mssql = "mssql";
    case postgresql = "postgresql";
    case odbc = "odbc";
}
