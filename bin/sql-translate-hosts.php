#!/usr/bin/env php
<?php
# encoding: utf-8
/**
 * sql-translate-hosts, a script for (WordPress) Database Migration
 * Take a SQL dump from one database, and perform host name translations so that
 * it can live in another environment--for example, allows a production database
 * to be ported over to a staging environment by translating www.example.com
 * to staging.example.com
 * IMPORTANT: Assumes that SQL string literals are in single quotes, not double quotes.
 *
 * USAGE:
 * $ sql-translate-hosts.php '/(\w+\.)example\.org/'='$1staging.example.org' < production.sql > staging.sql
 * $ sql-translate-hosts.php www.example.com=staging.example.com < production.sql > staging.sql
 * $ sql-translate-hosts.php '{"www.example.net":"staging.example.net"}' < production.sql > staging.sql
 * <?php
 *   $instance = new SQL_Translate_Hosts(array(
 *       'in_file'  => 'database/local.sql',
 *       'out_file' => 'database/dev.sql',
 *       'host_map' => array('local.example.com' => 'dev.example.com'),
 *   ));
 *   $instance->run();
 * ?>
 *<?php
 *   $instance = new SQL_Translate_Hosts(array(
 *       'host_map' => array('/(\w+\.)local\.example\.com/' => '$1dev.example.com'),
 *   ));
 *   $instance->run();
 * ?>
 *
 * Run tests:
 * $ sql-translate-hosts --test
 * <?php SQL_Translate_Hosts::test() ?>
 *
 * CHANGELOG:
 * 2011-07-07 Greatly improved performance by processing one line at a time.
 * 2012-02-03 Refactored into class, added tests, fixed multiple replacement in serialized strings.
 *
 * @todo Are SQL dumps ever done with double-quoted strings?
 *
 * @author Weston Ruter <weston@x-team.com> @westonruter
 * Copyright (C) 2012, X-Team <http://x-team.com/>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class SQL_Translate_Hosts {
	public $in_file;
	public $out_file;
	public $host_map;
	
	/**
	 * @param {array} $args in_file and out_file are streams (filenames, streams, or resources)
	 *                      'host_map' is an array where keys are domains to be replace and
	 *                      values are the replacements. The keys can be regexes
	 *                      indicated by initial and trailing '/'
	 */
	function __construct( array $args = array() ){
		$default_args = array(
			'in_file'  => 'php://stdin',
			'out_file' => 'php://stdout',
			'host_map' => array(),
		);
		$args = array_merge($default_args, $args);
		foreach( array_keys($default_args) as $arg_name ){
			$this->$arg_name = $args[$arg_name];
		}
	}
	
	/**
	 * Process the in_file and send it to out_file
	 * @return {int} Number of lines in the input
	 */
	function run(){
		
		// get input stream
		if(is_resource($this->in_file)){
			$fi = $this->in_file;
		}
		else {
			$fi = fopen($this->in_file,  'r');
			if($fi === false){
				throw new SQL_Translate_Hosts_Exception(sprintf('Unable to open "%s" for reading', $this->in_file));
			}
		}
		
		// get output stream
		if(is_resource($this->out_file)){
			$fo = $this->out_file;
		}
		else {
			$fo = fopen($this->out_file, 'w');
			if($fo === false){
				throw new SQL_Translate_Hosts_Exception(sprintf('Unable to open "%s" for writing', $this->out_file));
			}
		}
		
		// iterate over stream and process line-by-line
		$line_count = 0;
		while(($buffer = fgets($fi)) !== false){
			fputs($fo, $this->_replace_in_quoted_strings($buffer, array($this, '_handle_string_replacement'), "'"));
			$line_count += 1;
		}
		fclose($fo);
		fclose($fi);
		return $line_count;
	}
	
	/**
	 * Parse the $haystack for all quoted string literals and filter the
	 * contents through the $replace_callback.
	 * @param {string} $haystack
	 * @param {callable} $replace_callback
	 * @param {string} $quote_char
	 * @return {string}
	 */
	protected function _replace_in_quoted_strings($haystack, $replace_callback, $quote_char = '"'){
		$return = "";
		$buffer = "";
		$is_in_string = false;
		$escape_count = 0;
		
		for($i = 0, $len = strlen($haystack); $i < $len; $i += 1){
			if($haystack[$i] === $quote_char && $escape_count % 2 === 0){
				// Handle end of string
				if($is_in_string){
					$buffer = self::nomysql_real_unescape_string($buffer);
					$replaced = call_user_func($replace_callback, $buffer);
					$replaced = self::nomysql_real_escape_string($replaced);
					$return .= $quote_char . $replaced . $quote_char;
				}
				$buffer = '';
				$is_in_string = !$is_in_string;
			}
			else if($is_in_string){
				$buffer .= $haystack[$i];
			}
			// Outside of string
			else {
				$return .= $haystack[$i];
			}
			
			$escape_count = ($haystack[$i] === '\\') ? $escape_count+1 : 0;
		}
		return $return;
	}
	
	
	/**
	 * Given a string, replace the hosts in it, accounting for if the string is serialized PHP
	 * @param {string} $old_str
	 * @return {string}
	 */
	protected function _handle_string_replacement($old_str){
		$new_str = "";
		if(self::is_serialized_php($old_str)){
			$overall_offset = 0;
			while(preg_match('/\bs:(\d+):"/', $old_str, $matches, PREG_OFFSET_CAPTURE, $overall_offset)){
				$match_offset = $matches[0][1];
				$prefix_len = strlen($matches[0][0]);
				$serialized_str_len = (int)$matches[1][0];
				
				// Append whatever we've passed over
				$new_str .= substr($old_str, $overall_offset, $match_offset - $overall_offset);
				
				$old_serialized_str = substr($old_str, $match_offset + $prefix_len, $serialized_str_len);
				$ending_delimiter_pos = $match_offset + $prefix_len + $serialized_str_len;
				$ending_delimiter = substr($old_str, $ending_delimiter_pos, 1);
				if( $ending_delimiter !== '"' ){
					throw new SQL_Translate_Hosts_Exception(sprintf('Expected closing double-quote delimiter \'"\' but got \'%s\' at position %d in %s', $ending_delimiter, $ending_delimiter_pos, $old_str));
				}
				
				// Replace the hosts in the serialized string
				$new_serialized_str = $this->_replace_hosts($old_serialized_str);
				$serialized_str_len += strlen($new_serialized_str) - strlen($old_serialized_str);
				$new_str .= sprintf('s:%d:"%s"', $serialized_str_len, $new_serialized_str);
				
				$overall_offset = $match_offset + $prefix_len + strlen($old_serialized_str) + 1; // +1 for closing double-quote
			}
			$new_str .= substr($old_str, $overall_offset);
		}
		else {
			$new_str = $this->_replace_hosts($old_str);
		}
		return $new_str;
	}
	
	
	/**
	 * Given a totally-unescaped non-serialized string, do the substitutions using
	 * the $host_pattern and $host_replace constants, or the $host_map global.
	 * @param {string} $str
	 * @return {string}
	 */
	protected function _replace_hosts($str){
		
		$regex_delimiter = '#';
		$prefixes = array(
			'^',
			'\s',
			'“',
			'https?://', // @todo also allow scheme-less URLs?
			'https?%3A%2F%2F', #url-encoded
			'['.preg_quote('"\'`(){}[]@<>', $regex_delimiter).']'
		);
		
		foreach($this->host_map as $search => $replace){
			$pattern = $regex_delimiter;
			$pattern .= '('.join('|', $prefixes).')';
			
			// Check to see if $search is a regular expression
			if(preg_match('#^/.+/$#', $search)){
				$pattern .= addcslashes(trim($search, '/'), $regex_delimiter);
				$replace = '$1' . preg_replace(
					'/(?<=\$)(\d)/e',
					'intval($1)+1',
					$replace
				);
			}
			// Simple search and replace
			else {
				$pattern .= preg_quote($search, $regex_delimiter);
				$replace = '$1' . $replace;
			}
			$pattern .= '(?=\b|\s|”|\W|$)';
			$pattern .= $regex_delimiter;
			$str = preg_replace($pattern, $replace, $str);
		}
		return $str;
	}
	
	
	/**
	 * Used instead of mysql_real_escape_string() so that we don't have to connect
	 * to a MySQL server when we use the --sql option
	 * @param {string} $str
	 * @return {string}
	 */
	static function nomysql_real_escape_string($str){
		// mysql_real_escape_string() calls MySQL's library function
		// mysql_real_escape_string, which prepends backslashes to the following
		// characters: \x00, \n, \r, \, ', " and \x1a.
		$replacements = array(
			"\\"   => "\\\\",
			"\r"   => '\r',
			"\n"   => '\n',
			"\x00" => "\\0",
			"\x1a" => "\\\x1a",
			"'"    => "\\'",
		);
		
		return str_replace(
			array_keys($replacements),
			array_values($replacements),
			$str
		);
	}
	
	
	/**
	 * Implement a function which can unescape SQL-escaped strings
	 * @param {string} $escaped
	 * @return {string}
	 */
	static function nomysql_real_unescape_string($escaped){
		// See table at http://dev.mysql.com/doc/refman/5.0/en/string-syntax.html
		$replacements = array(
			#'""'    => '"',
			"''"    => "'",
			'\0'    => "\x00",
			"\\'"   => "'",
			'\"'    => '"',
			'\b'    => "\x08",
			'\n'    => "\x0A",
			'\r'    => "\x0D",
			'\t'    => "\x09",
			'\Z'    => "\x1A",
			'\\\\'  => '\\',
		);
		
		$unescaped = "";
		for($i = 0; $i < strlen($escaped); $i += 1){
			$cc = substr($escaped, $i, 2);
			if(isset($replacements[$cc])){
				$unescaped .= $replacements[$cc];
				$i += 1;
			}
			else {
				$unescaped .= substr($escaped, $i, 1);
			}
		}
		
		return $unescaped;
	}
	
	
	/**
	 * Check value to find if it was serialized.
	 *
	 * If $data is not an string, then returned value will always be false.
	 * Serialized data is always a string.
	 *
	 * @package WordPress
	 * @since 2.0.5
	 *
	 * @param mixed $data Value to check to see if was serialized.
	 * @return bool False if not serialized and true if it was.
	 * @see is_serialize()
	 */
	static function is_serialized_php( $data ) {
		// if it isn't a string, it isn't serialized
		if ( !is_string( $data ) )
			return false;
		$data = trim( $data );
		if ( 'N;' === $data )
			return true;
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
			return false;
		switch ( $badions[1] ) {
			case 'a' :
			case 'O' :
			case 's' :
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
					return true;
				break;
		}
		return false;
	}
	
	
	/**
	 * Behavior executed when invoked from command line
	 */
	static function cli_exec($argv){
		try {
			if( in_array('--test', $argv) ){
				self::test();
				exit(0);
			}
			
			if(count($argv) < 2){
				self::cli_die("Error: Expected one argument: the JSON mapping for host names.");
			}
			
			// Build hostmap from command-line args
			$host_map = array();
			foreach(array_slice($argv, 1) as $arg){
				$parsed_json = @json_decode($arg, true);
				if(is_array($parsed_json)){
					$host_map = array_merge($host_map, $parsed_json);
				}
				else if(strpos($arg, '=') !== false){
					list($src_host, $dest_host) = explode('=', $arg, 2);
					$host_map[$src_host] = $dest_host;
				}
			}
			if(empty($host_map)){
				SQL_Translate_Hosts::cli_die('Missing or malformed host-map provided');
			}
			
			$in_file = 'php://stdin';
			$out_file = 'php://stdout';
			
			$instance = new self(compact('host_map', 'in_file', 'out_file'));
			$instance->run();
			exit(0);
		}
		catch( Exception $e ){
			self::cli_die($e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * PHP's die() function doesn't output to STDERR and it doesn't exit the program
	 * with an error exit code. Fix this.
	 * @param {string} $msg
	 * @param {int} $exit_code
	 */
	static function cli_die($msg, $exit_code = 1){
		fwrite(STDERR, rtrim($msg) . "\n");
		exit($exit_code);
	}
	
	/**
	 * Run tests to ensure functionality is working
	 */
	static function test(){
		
		$test_cases = array(
			'URL in entire SQL string' => array(
				'host_map' => array('local.example.com' => 'www.example.com'),
				'in_sql'   => "INSERT INTO `wp_options` VALUES (1,0,'siteurl','http://local.example.com','yes')",
				'out_sql'  => "INSERT INTO `wp_options` VALUES (1,0,'siteurl','http://www.example.com','yes')",
			),
			'Multiple URL replacement' => array(
				'host_map' => array('local.example.com' => 'www.example.com', 'local.example.net' => 'www.example.net'),
				'in_sql'   => "INSERT INTO `fake_table` VALUES ('http://local.example.com','http://local.example.net')",
				'out_sql'  => "INSERT INTO `fake_table` VALUES ('http://www.example.com','http://www.example.net')",
			),
			'Pattern URL replacement' => array(
				'host_map' => array('/(\w+\.)?local\.example\.com/' => '$1example.com'),
				'in_sql'   => "INSERT INTO `fake_table` VALUES ('http://local.example.com', 'http://foo.local.example.com','http://bar.local.example.com')",
				'out_sql'  => "INSERT INTO `fake_table` VALUES ('http://example.com', 'http://foo.example.com','http://bar.example.com')",
			),
			'URL inside of HTML' => array(
				'host_map' => array('local.example.com' => 'www.example.com'),
				'in_sql'   => sprintf("INSERT INTO `fake_table` VALUES ('%s','yes')", '<p><a href="http://local.example.com/bar/">Bar</a></p>'),
				'out_sql'  => sprintf("INSERT INTO `fake_table` VALUES ('%s','yes')", '<p><a href="http://www.example.com/bar/">Bar</a></p>'),
			),
			'Serialized PHP replacement' => array(
				'host_map' => array('local.example.com' => 'www.example.com'),
				'in_sql'   => sprintf("INSERT INTO `fake_table` VALUES ('%s')", self::nomysql_real_escape_string(serialize(array('<p><a href="http://local.example.com/bar/">Bar</a></p>', "http://local.example.com/foo/",)))),
				'out_sql'  => sprintf("INSERT INTO `fake_table` VALUES ('%s')", self::nomysql_real_escape_string(serialize(array('<p><a href="http://www.example.com/bar/">Bar</a></p>', "http://www.example.com/foo/", )))),
			),
			'Serialized custom object replacement' => array(
				'host_map' => array('local.example.com' => 'www.example.com'),
				'in_sql'   => sprintf("INSERT INTO `fake_table` VALUES ('%s')", self::nomysql_real_escape_string('O:3:"Foo":2:{s:3:"baz";s:50:"<a href="http://local.example.com/hello">Hello</a>";s:4:"quux";s:36:"http://local.example.com/lorem-ipsum";}')),
				'out_sql'  => sprintf("INSERT INTO `fake_table` VALUES ('%s')", self::nomysql_real_escape_string('O:3:"Foo":2:{s:3:"baz";s:48:"<a href="http://www.example.com/hello">Hello</a>";s:4:"quux";s:34:"http://www.example.com/lorem-ipsum";}')),
			),
		);
		
		$start_green = "\033[32m";
		$start_red = "\033[31m";
		$end_color = "\033[37m";
		
		$fail_count = 0;
		$test_number = 0;
		foreach( $test_cases as $test_name => $test_case ){
			$test_number += 1;
			$actual_sql = '(unknown)';
			extract($test_case);
			try {
				$in_file = 'data://text/plain;base64,' . base64_encode($in_sql);
				$out_file = fopen('php://memory', 'w');
				$instance = new self(compact('in_file', 'out_file', 'host_map'));
				$instance->run();
				rewind($out_file);
				$actual_sql = stream_get_contents($out_file);
				if( $actual_sql !== $out_sql ){
					throw new Exception('Actual output not same as expected.');
				}
				printf("%02d. {$start_green}PASS: %s{$end_color}", $test_number, $test_name);
			}
			catch(Exception $e){
				printf("%02d. {$start_red}FAIL: %s{$end_color}", $test_number, $test_name);
				printf(', with exception (code %1$s): %2$s' . "\n", $e->getCode(), $e->getMessage());
				printf("    Source: %s\n", $in_sql);
				printf("    Expect: %s\n", $out_sql);
				printf("    Actual: %s\n", $actual_sql);
				$fail_count += 1;
			}
			print "\n";
		}
		print "------\n";
		printf("{$start_green}Passed: %d{$end_color}\n", count($test_cases) - $fail_count);
		printf("{$start_red}Failed: %d{$end_color}\n", $fail_count);
	}
	
}

class SQL_Translate_Hosts_Exception extends Exception {}

if( php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__) ){
	SQL_Translate_Hosts::cli_exec($argv);
}
