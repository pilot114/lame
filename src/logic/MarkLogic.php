<?php
require_once( "src/parse/Parser.php" );
require_once( "src/parse/StringReader.php" );
require_once( "src/parse/Context.php" );
require_once( "src/logic/ML_Interpreter.php" );

// каждый хэндлер будет вытаскивать из контекста одно или несколько значений
// и ложить в него заменяющий их Expression
class StringLiteralHandler implements Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $value = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new LiteralExpression( $value ) );
    }
}
class EqualsHandler implements Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new EqualsExpression( $comp1, $comp2 ) );
    }
}
class VariableHandler implements Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $varname = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new VariableExpression( $varname ) );
    }
}
class BooleanOrHandler implements Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new BooleanOrExpression( $comp1, $comp2 ) );
    }
}
class BooleanAndHandler implements Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new BooleanAndExpression( $comp1, $comp2 ) );
    }
}

class MarkParse {
    private $expression;
    private $operand;
    private $interpreter;
    private $context;

    function __construct( $statement ) {
        $this->compile( $statement );
    }

    function evaluate( $input ) {
        $icontext = new InterpreterContext();
        $prefab = new VariableExpression('input', $input );
        // add the input variable to Context
        $prefab->interpret( $icontext );
 
        $this->interpreter->interpret( $icontext );
        $result = $icontext->lookup( $this->interpreter );
        return $result;
    }

    function compile( $statement_str ) {
        // build parse tree
        $context = new Context();
        $scanner = new Scanner( new StringReader($statement_str), $context );
        $statement = $this->expression();
        $scanresult = $statement->scan( $scanner );
         
        if ( ! $scanresult || $scanner->tokenType() != Scanner::EOF ) {
            $msg  = "";
            $msg .= " line: {$scanner->line_no()} ";
            $msg .= " char: {$scanner->char_no()}";
            $msg .= " token: {$scanner->token()}\n";
            throw new Exception( $msg );
        }
 
        $this->interpreter = $scanner->getContext()->popResult();
    }
    // для каждого statement описывается последовательность токенов и по необходимости
    // добавляется хэндлер


    // OPERAND (OR OPERAND | AND OPERAND)*
    function expression() {
        if ( ! isset( $this->expression ) ) {
            $this->expression = new SequenceParse();
            $this->expression->add( $this->operand() );
            $bools = new RepetitionParse( );
            $whichbool = new AlternationParse();
            $whichbool->add( $this->orExpr() );
            $whichbool->add( $this->andExpr() );
            $bools->add( $whichbool );
            $this->expression->add( $bools );
        }
        return $this->expression;
    }

    // OR OPERAND
    function orExpr() {
        $or = new SequenceParse();
        $or->add( new WordParse('or') )->discard();
        $or->add( $this->operand() );
        $or->setHandler( new BooleanOrHandler() );
        return $or;
    }

    // AND OPERAND
    function andExpr() {
        $and = new SequenceParse();
        $and->add( new WordParse('and') )->discard();
        $and->add( $this->operand() );
        $and->setHandler( new BooleanAndHandler() );
        return $and;
    }

    // ("("EXPRESSION")" | STRING | VAR )(equals OPERAND)*
    function operand() {
        if ( ! isset( $this->operand ) ) {
            $this->operand = new SequenceParse( );
            $comp = new AlternationParse( );
            $exp = new SequenceParse( );
            $exp->add( new CharacterParse( '(' ))->discard();
            $exp->add( $this->expression() );
            $exp->add( new CharacterParse( ')' ))->discard();
            $comp->add( $exp ); 
            $comp->add( new StringLiteralParse() )
                ->setHandler( new StringLiteralHandler() ); 
            $comp->add( $this->variable() );
            $this->operand->add( $comp );
            $this->operand->add( new RepetitionParse( ) )->add($this->eqExpr());
        }
        return $this->operand;
    }

    // equals OPERAND
    function eqExpr() {
        $equals = new SequenceParse();
        $equals->add( new WordParse('equals') )->discard();
        $equals->add( $this->operand() );
        $equals->setHandler( new EqualsHandler() );
        return $equals;
    }

    // $WORD
    function variable() {
        $variable = new SequenceParse();
        $variable->add( new CharacterParse( '$' ))->discard();
        $variable->add( new WordParse());
        $variable->setHandler( new VariableHandler() );
        return $variable;
    }
}
?>
