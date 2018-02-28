<?php

namespace ApolloRIP\Logchecker\Parser\EAC;

use ApolloRIP\Logchecker\Parser\AbstractParser;

class EACParser extends AbstractParser {
	protected $program = 'Exact Audio Copy';
	protected $versions = [
		"1.3" => "2. September 2016",
        "1.2" => "12. August 2016",
        "1.1" => "23. June 2015",
        "1.0 beta 6" => "9. April 2015",
        "1.0 beta 5" => "2. April 2015",
        "1.0 beta 4" => "7. December 2014",
        "1.0 beta 3" => "29. August 2011",
        "1.0 beta 2" => "29. April 2011",
        "1.0 beta 1" => "15. November 2010",
        "0.99 prebeta 5" => "4. May 2009",
        "0.99 prebeta 4" => "23. January 2008",
        "0.99 prebeta 3" => "28. July 2007",
        "0.99 prebeta 2" => "28 July 2007",
        "0.99 prebeta 1" => "25. May 2007"
	];

	protected $language_identifiers = [
		'ru' => [
			'Отчёт EAC об извлечении, выполненном'
		],
		'bg' => [
			'Отчет на EAC за извличане, извършено на'
		],
		'cs' => [
			'Protokol extrakce EAC z'
		],
		'nl' => [
			'EAC uitlezen log bestand van'
		],
		'de' => [
			'EAC Auslese-Logdatei vom'
		],
		'it' => [
			"File di log EAC per l'estrazione del"
		],
		'pl' => [
			'Sprawozdanie ze zgrywania programem EAC z'
		],
		'zh-Hans' => [
			'EAC 抓取日志文件从'
		],
		'sr' => [
			'EAC-ov fajl dnevnika ekstrakcije iz'
		],
		'sk' => [
			'EAC log súbor extrakcie z'
		],
		'es' => [
			'Archivo Log de extracciones desde'
		],
		'sv' => [
			'EAC extraheringsloggfil från'
		],
		'fr' => [
			"Journal d'extraction EAC depuis"
		],
		'jp' => [
			'EAC 展開 ログファイル 日付',
			'EAC 取り込みログファイル 日付'
		]
	];

	private $languages = [];

	private function getLanguageStrings($language) {
		if (!isset($this->languages[$language])) {
			/** @noinspection PhpIncludeInspection */
			$this->languages[$language] = require_once(__DIR__.'/Languages/' . $language . '.php');
		}
		return $this->languages[$language];
	}

	private function translateLog() {
		$english_dict = $this->getLanguageStrings('en');
		foreach ($this->language_identifiers as $language => $identifiers) {
			if (preg_match('/('.implode('|', array_map(function($identifier) { return preg_quote($identifier, "/"); }, $identifiers)).')/ui', $this->log) === 1) {
				$language_dict = $this->getLanguageStrings($language);
				$this->addDetail("Translated log from {$language_dict[1]} ({$language_dict[2]}) to {$english_dict[1]}.", 0, true);
				foreach ($language_dict as $key => $values) {
					if (!is_array($values)) {
						$values = [$values];
					}
					$log = preg_replace('/('.implode('|', array_map(function($val) { return preg_quote($val, '/'); }, $values)).')/ui', $english_dict[$key], $this->log);
					// In-case the preg_replace fails, it'll set $log to null, so we want to protect against losing our
					// entire log if that happens
					if ($log !== null) {
						$this->log = $log;
					}
				}
				// We expect to have a log in just one language
				break;
			}
		}
	}

	protected function preParse() {
		$this->translateLog();
	}

	protected function splitLogs() {
		// Split the log apart
		if (preg_match("/[\=]+\s+Log checksum/i", $this->log)) { // eac checksum
			$this->logs = preg_split("/(\n\=+\s+Log checksum.*)/i", $this->log, -1, PREG_SPLIT_DELIM_CAPTURE);
		} else { //no checksum
			$this->checksum = false;
			$this->logs = preg_split("/(\nEnd of status report)/i", $this->log, -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($this->logs as $Key => $Value) {
				if (preg_match("/---- CUETools DB Plugin V.+/i", $Value)) {
					unset($this->logs[$Key]);
				}
			}
		}

		// Strip any empty logs, and append the end line we split on to previous log
		foreach ($this->logs as $key => $log) {
			$log = trim($log);
			if ($log === "" || preg_match('/^\-+$/i', $log)) {
				unset($this->logs[$key]);
			}
			elseif (!$this->checksum && preg_match("/End of status report/i", $log)) {
				$this->logs[$key - 1] .= $log;
				unset($this->logs[$key]);
			}
			elseif ($this->checksum && preg_match("/[\=]+\s+Log checksum/i", $log)) {
				$this->logs[$key - 1] .= $log;
				unset($this->logs[$key]);
			}
		}
	}

	protected function parseRipDetails($log_index) {
		$log =& $this->logs[$log_index];
		if (preg_match('/Exact Audio Copy V(.+) from (.+)/', $log, $matches) === 1) {
			$version = $matches[1];
			$version_date = $matches[2];
			if (isset($this->versions[$version])) {
				if ($this->versions[$version] !== $version_date) {
					$this->addDetail('Invalid date for EAC version V'.$version, -30);
				}
			}
		}
		else {
			$this->addDetail('EAC version older than 0.99', -30);
			$this->checksum = false;
		}
	}

	protected function validateChecksum($log_index) {
		$wine_exists = trim(shell_exec('which wine')) !== '';
		$exe = '/usr/local/bin/eac_logchecker.exe';
		if ($wine_exists && file_exists($exe)) {
			$script = "script -q -c 'wine {$exe} {$this->log_path}' /dev/null";
			$out = shell_exec($script);
			if (strpos($out, 'Log entry has no checksum!') !== false ||
				strpos($out, 'Log entry was modified, checksum incorrect!') !== false ||
				strpos($out, 'Log entry is fine!') === false) {
				$this->checksum = false;
			}
		}

	}
}