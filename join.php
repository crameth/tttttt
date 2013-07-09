<?php
// for handling player joins
include('session.php');
include('logic.php');

if ( isset( $_POST['index'] ) && isset( $_POST['name'] ) )
{
	$index = $_POST['index'];
	$name = $_POST['name'];

	// stripping whitespace
	$name = preg_replace( '/\s+/', ' ', $name );

	// stripping special chars
	$name = htmlspecialchars($name);

	if ( strlen( $name ) > 0 ) // validate player name
	{
		play( $index, $name );

		// set session vars
		$_SESSION['index'] = $index;
		$_SESSION['name'] = $name;
		$_SESSION['timeout'] = time();
	}
}

header('location:index.php');
?>