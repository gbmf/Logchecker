<?php

namespace ApolloRIP\Logchecker;

use ApolloRIP\Logchecker\Parser\AbstractParser;
use ApolloRIP\Logchecker\Parser\EAC\EACParser;
use ApolloRIP\Logchecker\Parser\Whipper\WhipperParser;
use ApolloRIP\Logchecker\Parser\XLD\XLDParser;
use Yupmin\PHPChardet\Chardet;

class Logchecker {
	private $version = "3.0.0";

	private $chardet;
	private $log_path;
	private $log;
	/** @var AbstractParser */
	private $parser = null;

	public function __construct() {
		$this->chardet = new Chardet();
	}

	public function getLogcheckerVersion() {
		return $this->version;
	}

	public function newFile($log_path) {
		$this->reset();
		$this->log_path = $log_path;
		$this->log = file_get_contents($this->log_path);
		$this->normalizeLog();
		$this->getParser();
	}

	private function normalizeLog() {
		if (ord($this->log[0]) . ord($this->log[1]) == 0xFF . 0xFE) {
			$this->log = mb_convert_encoding(substr($this->log, 2), 'UTF-8', 'UTF-16LE');
		}
		elseif (ord($this->log[0]) . ord($this->log[1]) == 0xFE . 0xFF) {
			$this->log = mb_convert_encoding(substr($this->log, 2), 'UTF-8', 'UTF-16BE');
		}
		elseif (ord($this->log[0]) == 0xEF && ord($this->log[1]) == 0xBB && ord($this->log[2]) == 0xBF) {
			$this->log = substr($this->log, 3);
		}
		else {
			$chardet_container = $this->chardet->analyze($this->log_path);
			if ($chardet_container->getCharset() !== 'utf-8' && $chardet_container->getConfidence() > 0.7) {
				$this->log = mb_convert_encoding($this->log, 'UTF-8', $chardet_container->getCharset());
			}
		}
		$this->log = str_replace(array("\r\n", "\r"), "\n", $this->log);
	}

	private function getParser() {
		$first_line = strstr($this->log, "\n", true);
		if (preg_match('/^X Lossless Decoder/', $first_line)) {
			$parser = XLDParser::class;
		}
		elseif (preg_match('/Log created by: whipper/', $first_line)) {
			$parser = WhipperParser::class;
		}
		elseif (preg_match('/EAC/', $first_line) || preg_match('/Exact Audio Copy/', $first_line)) {
			$parser = EACParser::class;
		}
		else {
			return;
		}
		$this->parser = new $parser($this->log_path, $this->log);
	}

	public function parse($validate_checksum = true) {
		if ($this->parser !== null) {
			$this->parser->parse($validate_checksum);
		}
	}

	public function getProgram() {
		return $this->parser->getProgram();
	}

	public function getVersion() {

	}

	public function getScore() {
		return $this->parser->getScore();
	}

	public function getDetails() {
		return $this->parser->getDetails();
	}

	public function hasValidChecksum() {
		return $this->parser->hasValidChecksum();
	}

	public function reset() {

	}
}