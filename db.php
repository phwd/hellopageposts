<?php

class db {

  var $table = array('facebook_data');
  var $facebook_data;
  var $charset;
  var $collate;
  var $dbuser;
  var $dbpassword;
  var $dbname;
  var $dbhost;
  var $ready;

  function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {

    $this->init_charset();

    $this->dbuser = $dbuser;
    $this->dbpassword = $dbpassword;
    $this->dbname = $dbname;
    $this->dbhost = $dbhost;

    $this->db_connect();
  }

  function init_charset() {
    if ( defined( 'DB_COLLATE' ) ) 
      $this->collate = DB_COLLATE;

    if ( defined( 'DB_CHARSET' ) )
      $this->charset = DB_CHARSET;
  }

  function select( $db, $dbh = null ) {
    if ( is_null($dbh) )
      $dbh = $this->dbh;

    if ( !@mysql_select_db( $db, $dbh ) ) {
      $this->ready = false;
      return;
    }
  }

  function db_connect() {
    $this->dbh = @mysql_connect( $this->dbhost, $this->dbuser, $this->dbpassword, true );

    if ( !$this->dbh ) {
      return;
    }

    $this->ready = true;
    $this->select( $this->dbname, $this->dbh );
  }

  function query( $query ) {
    if ( ! $this->ready )
      return false;

    $this->result = @mysql_query( $query, $this->dbh );
    $this->num_queries++;

    if (!$this->result) {
      die('Invalid query: ' . mysql_error());
    }

    return $this->result;
  }

}
?>
