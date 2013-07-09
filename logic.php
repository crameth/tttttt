<?php
function render() // redraws the entire tictactoe tree based on json
{
	$cells = read( 'data/data.json' );

	// translate into js script that updates sections

	renderPlayers( $cells );
	renderStatus( $cells );
	renderTable( $cells );
}

function draw( $outer, $inner, $turn ) // must remember this is only data validation and input
{
	$cells = read( 'data/data.json' );

	$started = $cells['started'];
	$turn = $cells['turn'];

	if ( $started && $_SESSION['index'] == $turn % 2 + 1 ) // if game already started and user is the person who is suppose to draw
	{
		// reset skip status

		// update outer and turn
		$cells['outer'] = $inner;
		$cells['turn'] = $turn+1;

		// update cell
		$cells[$outer][$inner] = $turn%2+1;

		// update activity timeout
		$cells['activity'] = time();
	}

	for ( $i = 1 ; $i < 10 ; ++$i )
	{
		if ( $cells[$i]['winner'] < 1 )
		{
			$cells[$i]['winner'] = miniwin( $cells[$i] ); // 0 if not won, 1 if player 1, 2 if player 2
		}
	}
	
	$winner = $cells['winner'];

	if ( $winner == 0 )
		$cells['winner'] = win( $cells ); // -1 if draw, 0 if not won, 1 if player 1, 2 if player 2

	$winner = $cells['winner'];

	if ( $winner != 0 )
		$cells['started'] = false; // started should determine whether players can join/draw, other than representing whether a game has started or not

	$next = $cells['outer']; // is the board that will be played next, don't mix up with $outer, which is the current board where draw is taking place
	
	if ( $next > 0 && $cells[$next]['winner'] > 0 ) // if board is already won
	{
		if ( $cells[$next]['full'] ) // if full
		{
			$cells['outer'] = 0; // set to anywhere on board
		}
		else // if not full, according to json, check if that's really the case
		{
			$i = 1;

			while ( $cells[$next][$i] != 0 && $i < 10 ) // check if board is full
				++$i;

			if ( $i > 9 ) // if board is full, set to full and set outer to 0
			{
				$cells[$next]['full'] = true;
				$cells['outer'] = 0;
			}
		}
	}

	write( 'data/data.json', $cells );
}

function play( $index, $name )
{
	$cells = read( 'data/data.json' );

	$started = $cells['started'];

	if ( !$started )
	{
		$player1 = $cells['player1'];
		$player2 = $cells['player2'];

		// update player name
		if ( $index < 2 && strlen( $player1 ) < 1 && $name != $player2 ) // player 1
			$cells['player1'] = $name;
		else if ( $index > 1 && strlen ( $player2 ) < 1 && $name != $player1 ) // player 2
			$cells['player2'] = $name;

		// check if game can be started
		if ( strlen( $cells['player1'] ) > 0 && strlen ( $cells['player2'] ) > 0 )
		{
			$cells['started'] = true;
			$cells['activity'] = time();
		}
	}

	write( 'data/data.json', $cells );
}


function renderPlayers( $cells )
{
	$logged = logged();

	$player1 = $cells['player1'];
	$player2 = $cells['player2'];

	echo '<div id="players">';
	echo '<div class="player">';

	echo '<img src="img/o.png" alt="" />';
	echo '<h2>Player 1</h2>';

	if ( strlen( $player1 ) > 0 || $logged )
	{
		echo $player1;
	}
	else
	{
		echo '<form action="join.php" method="POST">';
		echo '<input name="index" type="hidden" value="1" />';
		echo '<input name="name" type="text" placeholder="Enter your name" /><br />';
		echo '<input type="submit" value="Join!" />';
		echo '</form>';
	}

	echo '</div>';
	echo '<div class="player">';

	echo '<img src="img/x.png" alt="" />';
	echo '<h2>Player 2</h2>';

	if ( strlen( $player2 ) > 0 || $logged )
	{
		echo $player2;
	}
	else
	{
		echo '<form action="join.php" method="POST">';
		echo '<input name="index" type="hidden" value="2" />';
		echo '<input name="name" type="text" placeholder="Enter your name" /><br />';
		echo '<input type="submit" value="Join!" />';
		echo '</form>';
	}

	echo '</div>';
	echo '</div>';
}

function renderStatus( $cells )
{
	$winner = $cells['winner'];
	$started = $cells['started'];
	$player1 = $cells['player1'];
	$player2 = $cells['player2'];
	$outer = $cells['outer'];
	$turn = $cells['turn'] % 2 + 1;
	$skip = $cells['skip'];

	echo '<div id="status" class="clear">';
	
	if ( $started )
	{
		if ( logged() )
		{
			if ( $outer > 0 && $cells[$outer]['winner'] > 0 ) // if oppo sends player to board that's already won
			{
				if ( $_SESSION['index'] == $turn )
				{
					if ( $cells[$outer]['full'] )
						echo "Your opponent sent you to a full board. You are free to make a move anywhere else.";
					else
						echo 'Your turn. You have 15 minutes to make a move.';
				}
				else
				{
					if ( $cells[$outer]['full'] )
						echo "You sent your opponent to a full board. They are now free to make a move anywhere else.";
					else
						echo 'Your opponent\'s turn. Please wait for your opponent to make a move.';
				}
			}
			else
			{
				if ( $_SESSION['index'] == $turn )
					echo 'Your turn. You have 15 minutes to make a move.';
				else
					echo 'Your opponent\'s turn. Please wait for your opponent to make a move.';
			}
		}
		else
		{
			if ( $turn % 2 )
				echo $player2.'\'s turn.';
			else
				echo $player1.'\'s turn.';
		}
	}
	else if ( $winner != 0 )
	{
		switch ($winner)
		{
			case -1:
				echo 'This game is a draw. Boring!';
				break;
			case 1:
				echo $player1.' won the game!';
				break;
			case 2:
				echo $player2.' won the game!';
				break;
		}
	}
	else
	{
		if ( strlen( $player1 ) > 0 )
			echo 'Waiting for player 2.';
		else if ( strlen( $player2 ) > 0 )
			echo 'Waiting for player 1.';
		else
			echo 'Waiting for players.';
	}

	echo '</div>';
}

function renderTable( $cells )
{
	$started = $cells['started'];
	$outer = $cells['outer'];
	$turn = $cells['turn'];

	echo '<table>';

	for ( $i = 1; $i < 10; ++$i )
	{
		if ( $i % 3 == 1 )
			echo '<tr>';

		if ( $cells[$i]['winner'] > 0 )
		{
			if ( $cells[$i]['winner'] == 1 )
				echo '<td class="one">';
			else
				echo '<td class="two">';
		}
		else
		{
			echo '<td>';
		}
		echo '<table>';

		for ( $j = 1; $j < 10; ++$j )
		{
			if ( $j % 3 == 1 )
				echo '<tr>';

			$cell = $cells[$i][$j];

			if ( $cell > 0 )
			{
				if ( $cell == 1)
					echo '<td class="circle">&nbsp;</td>';
				else
					echo '<td class="cross">&nbsp;</td>';
			}
			else
			{
				if ( logged() && isset( $_SESSION['index'] ) && $_SESSION['index'] == $turn%2+1 && ( $outer < 1 || $outer == $i ) && $started )
					echo '<td><a href="javascript:draw('.$i.','.$j.','.$turn.');">/</a></td>';
				else
					echo '<td></td>';
			}

			if ( $j % 3 == 0 )
				echo '</tr>';
		}

		echo '</table>';
		echo '</td>';

		if ( $i % 3 == 0 )
			echo '</tr>';
	}
	
	echo '</table>';
}

function miniwin( $cells )
{
	$winner = 0;

	if ( $cells['winner'] < 1 ) // only check if the board hasn't already been won
	{
		if ( $cells[1] > 0 )
		{
			if ( $cells[1] == $cells[2] && $cells[1] == $cells[3] ) // 123
			{
				if ( $cells[1] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			if ( $cells[1] == $cells[4] && $cells[1] == $cells[7] ) // 147
			{
				if ( $cells[1] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			if ( $cells[1] == $cells[5] && $cells[1] == $cells[9] ) // 159
			{
				if ( $cells[1] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
		}

		if ( $cells[2] > 0 && $cells[2] == $cells[5] && $cells[2] == $cells[8] ) // 258
		{
			if ( $cells[2] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}

		if ( $cells[3] > 0 )
		{
			if ( $cells[3] == $cells[5] && $cells[3] == $cells[7] ) // 357
			{
				if ( $cells[3] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			if ( $cells[3] == $cells[6] && $cells[3] == $cells[9] ) // 369
			{
				if ( $cells[3] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
		}

		if ( $cells[4] > 0 && $cells[4] == $cells[5] && $cells[4] == $cells[6] ) // 456
		{
			if ( $cells[4] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}

		if ( $cells[7] > 0 && $cells[7] == $cells[8] && $cells[7] == $cells[9] ) // 789
		{
			if ( $cells[7] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}
	}

	return $winner;
}

function win( $cells )
{
	$winner = 0;

	if ( $cells['winner'] < 1 ) // only check if the game hasn't already been won
	{
		if ( $cells[1]['winner'] > 0 )
		{
			if ( $cells[1]['winner'] == $cells[2]['winner'] && $cells[1]['winner'] == $cells[3]['winner'] ) // 123
			{
				if ( $cells[1]['winner'] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			else if ( $cells[1]['winner'] == $cells[4]['winner'] && $cells[1]['winner'] == $cells[7]['winner'] ) // 147
			{
				if ( $cells[1]['winner'] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			else if ( $cells[1]['winner'] == $cells[5]['winner'] && $cells[1]['winner'] == $cells[9]['winner'] ) // 159
			{
				if ( $cells[1]['winner'] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}

			++$draw;
		}

		if ( $cells[2]['winner'] > 0 && $cells[2]['winner'] == $cells[5]['winner'] && $cells[2]['winner'] == $cells[8]['winner'] ) // 258
		{
			if ( $cells[2]['winner'] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}
		else
		{
			++$draw;
		}

		if ( $cells[3]['winner'] > 0 )
		{
			if ( $cells[3]['winner'] == $cells[5]['winner'] && $cells[3]['winner'] == $cells[7]['winner'] ) // 357
			{
				if ( $cells[3]['winner'] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			else if ( $cells[3]['winner'] == $cells[6]['winner'] && $cells[3]['winner'] == $cells[9]['winner'] ) // 369
			{
				if ( $cells[3]['winner'] == 1 )
					$winner = 1;
				else
					$winner = 2;
			}
			else
			{
				++$draw;
			}
		}

		if ( $cells[4]['winner'] > 0 && $cells[4]['winner'] == $cells[5]['winner'] && $cells[4]['winner'] == $cells[6]['winner'] ) // 456
		{
			if ( $cells[4]['winner'] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}
		else
		{
			++$draw;
		}

		if ( $cells[7]['winner'] > 0 && $cells[7]['winner'] == $cells[8]['winner'] && $cells[7]['winner'] == $cells[9]['winner'] ) // 789
		{
			if ( $cells[7]['winner'] == 1 )
				$winner = 1;
			else
				$winner = 2;
		}
		else
		{
			++$draw;
		}
	}

	if ($draw > 4) // every failed check for win when the winner isn't 0 for a minor board adds 1 to draw, which means after all 5 checks are complete, if draw is 5, the game is considered drawn
		$winner = -1;

	return $winner;
}

function check() // check if activity timeout has been breached
{
	$cells = read( 'data/data.json' );

	$started = $cells['started'];
	$winner = $cells['winner'];
	$timeout = $cells['activity'];

	if ( ( $started || $winner != 0 ) && $timeout + 15*60 < time() ) // 15 minutes timeout
		restart();
}

function restart() // resets json file to brand new game
{
	$cells = read( 'data/data.json' );

	$cells['winner'] = 0;
	$cells['player1'] = '';
	$cells['player2'] = '';
	$cells['started'] = false;
	$cells['activity'] = 0;
	$cells['outer'] = 0;
	$cells['turn'] = 0;

	for ( $i = 1 ; $i < 10 ; ++$i )
	{
		$cells[$i]['winner'] = 0;
		$cells[$i]['full'] = false;

		for ( $j = 1 ; $j < 10 ; ++$j )
		{
			$cells[$i][$j] = 0;
		}
	}

	write( 'data/data.json', $cells );
}


// io ops

function read( $filename )
{
	$json = file_get_contents( $filename );
	$cells = json_decode( $json, true );

	return $cells;
}
function write( $filename, $cells )
{
	$json = json_encode( $cells );
	file_put_contents( $filename, $json );
}

function logged()
{
	$logged = false;

	if ( isset( $_SESSION['index'] ) && isset( $_SESSION['name'] ) && isset( $_SESSION['timeout'] ) )
	{
		$logged = true;
	}

	return $logged;
}
function playing( $player ) // checks if session stored player variable exists inside json, if not, destroy session
{
	$playing = true;

	$cells = read( 'data/data.json' );
	$name = '';

	if ( $player == 1 )
		$name = $cells['player1'];
	else
		$name = $cells['player2'];
	
	if ( $name != $player )
		session_destroy();
}
?>