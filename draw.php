<?php
// for handling player joins
include('session.php');
include('logic.php');

/* todo:
check for wins (store inside json?)
*/

$logged = logged();

if ( $logged )
{
	if ( $_SESSION['timeout'] + 15*60 < time() ) // 15 minutes timeout
	{
		session_destroy(); // problem with this is that you keep calling logged(), which makes it destroy session multiple times in a single millisecond

		check();
	}
	else if ( isset( $_POST['outer'] ) && isset( $_POST['inner'] ) && isset( $_POST['turn'] ) )
	{
		$outer = $_POST['outer'];
		$inner = $_POST['inner'];
		$turn = $_POST['turn'];

		if ( ( $outer > 0 && $outer < 10 ) && ( $inner > 0 && $inner < 10 ) && ( $turn > -1 ) )
		{
			draw( $outer, $inner, $turn );
			
			$_SESSION['timeout'] = time();
		}
	}
	
	// header('location:index.php');
}
?>