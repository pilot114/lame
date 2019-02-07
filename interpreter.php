<?php
require( 'src/logic/MarkLogic.php' );

$input = 'hello';
$statement = file_get_contents("source2.lame");

$engine = new MarkParse( $statement );
// на данном этапе уже получена последовательность команд. теперь выполняем
$result = $engine->evaluate( $input );
var_dump($result);

?>
