<?php

require_once("src/parse/Scanner.php");
require_once("src/parse/Context.php");
require_once("src/parse/StringReader.php");

$user_in = file_get_contents("source2.lame");

$context = new Context();
$reader = new StringReader( $user_in );
$scanner = new Scanner( $reader, $context );

while ( $scanner->nextToken() != Scanner::EOF ) {
    print $scanner->token();
    print "\t{$scanner->line_no()}";
    print "\t{$scanner->char_no()}";
    print "\t{$scanner->getTypeString()}\n";
}
