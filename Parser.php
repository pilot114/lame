<?php

class Parser
{
	private $code;

	// return aligned 2-bytes HEX value
	private function uint($number)
	{
		$n = intval($number);
		if( $n >= 0 && $n < 256) {
			return str_pad(dechex($n), 2, "0", STR_PAD_LEFT);
		} else {
			echo "Ups, overflow";
		}
	}

	// " std functions"
	private function result($number)
	{
		return "6a" . $this->uint($number) . " 58c3";
	}

	public function __construct($source)
	{
		// one string - one command
		$strings = explode(PHP_EOL, $source);
		foreach ($strings as $s) {
			// booo
			if (preg_match("#^result\((.+)\)$#", $s, $match)) {
				$this->code .= $this->result($match[1]);
			} else {
				echo "Parser: what is '$s'?";
				die();
			}
		}
	}

	public function getCode()
	{
		return $this->code;
	}
}