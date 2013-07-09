<?php
session_start();

include('logic.php');

check();

if ( logged() && $_SESSION['timeout'] + 15*60 < time() ) // 15 minutes timeout
{
	$player = $_SESSION['name'];

	playing( $player );
}

//session_destroy();
//restart();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Tic-Tic-Tac-Toe-Tac-Toe</title>

	<script src="js/jquery-1.8.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.periodicalupdater.min.js" type="text/javascript"></script>
	<script src="js/tttttt.js" type="text/javascript"></script>
	
	<link href='http://fonts.googleapis.com/css?family=Happy+Monkey' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="reset.css">
	<link rel="stylesheet" type="text/css" href="style.css">

	<style type="text/css">
		
	</style>

	<script type="text/javascript">
	</script>
</head>

<body>
	<h1>Tic-Tic-Tac-Toe-Tac-Toe</h1>
	<?php render(); ?>
	<div id="about">
		<a href="http://www.facebook.com/Adinth/posts/10151776989527189" target="_blank">http://www.facebook.com/Adinth/posts/10151776989527189</a>
		<a href="http://mathwithbaddrawings.com/2013/06/16/ultimate-tic-tac-toe/" target="_blank">http://mathwithbaddrawings.com/2013/06/16/ultimate-tic-tac-toe/</a>
	</div>
	<div id="dev">
		Todo:
		<ul>
		<li>player timeouts and game timeouts don't sync</li>
		<li>js then optimize</li>
		</ul>
	</div>
</body>

</html>