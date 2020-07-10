<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

abstract class CParser {

	const PARSE_FAIL = -1;
	const PARSE_SUCCESS = 0;
	const PARSE_SUCCESS_CONT = 1;

	protected $length = 0;
	protected $match = '';
	protected $error = '';
	protected $error_source = false;
	protected $error_pos = 0;

	/**
	 * Try to parse the string starting from the given position.
	 *
	 * @param string	$source		string to parse
	 * @param int 		$pos		position to start from
	 *
	 * @return int
	 */
	abstract public function parse($source, $pos = 0);

	/**
	 * Returns length of the parsed element.
	 *
	 * @return int
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * Returns parsed element.
	 *
	 * @return string
	 */
	public function getMatch() {
		return $this->match;
	}

	/**
	 * Returns the error message if string is invalid.
	 *
	 * @return string
	 */
	public function getError() {
		if ($this->error !== '') {
			return $this->error;
		}
		else if ($this->error_source !== false) {
			// The error message is prepared here to avoid extra calculations, if error message is not used.
			return $this->errorPosMessage($this->error_source, $this->error_pos);
		}
		else {
			return '';
		}
	}

	/**
	 * Save error source string and position for later use, when error will be retrieved.
	 *
	 * @param string $error
	 */
	protected function errorMessage($error) {
		$this->error = $error;
		$this->error_source = false;
		$this->error_pos = 0;
	}

	/**
	 * Save error source string and position for later use, when error will be retrieved.
	 *
	 * @param string $source
	 * @param int $pos
	 */
	protected function errorPos($source, $pos) {
		$this->error = '';
		$this->error_source = $source;
		$this->error_pos = $pos;
	}

	/**
	 * Prepares error message for incorrect syntax at position.
	 *
	 * @param string $source
	 * @param int $pos
	 *
	 * @return string
	 */
	protected function errorPosMessage($source, $pos) {
		$maxChunkSize = 50;
		$chunk = substr($source, $pos, $maxChunkSize);
		if (strlen($source) > $maxChunkSize + $pos) {
			$chunk .= ' ...';
		}

		return _s('incorrect syntax near "%1$s"', $chunk);
	}
}
