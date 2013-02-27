<?php
/**
 * Helper functions during CLI execution, each prefixed with cli_ to avoid name crashing.
 */


// Used to collect messages and errors temporarily. See chmod() as an example.
global $_messages, $_errors;

/**
 * Change the mode on a directory structure recursively. This includes changing the mode on files as well.
 *
 * @param string $path The path to chmod
 * @param integer $mode octal value 0755
 * @param boolean $recursive chmod recursively, set to false to only change the current directory.
 * @param array $exceptions array of files, directories to skip
 * @return boolean Returns TRUE on success, FALSE on failure
 */
function cli_chmod($path, $mode = 0755, $recursive = true, $exceptions = array()) {
  global $_messages, $_errors;

  if ($recursive === false && is_dir($path)) {
      if (@chmod($path, intval($mode, 8))) {
          $_messages[] = sprintf('<success>%s changed to %s<success>', $path, $mode);
          return true;
      }

      $_errors[] = sprintf('<error>%s NOT changed to %s', $path, $mode);
      return false;
  }

  if (is_dir($path)) {
      $paths = cli_tree($path);

      foreach ($paths as $type) {
          foreach ($type as $key => $fullpath) {
              $check = explode(DS, $fullpath);
              $count = count($check);

              if (in_array($check[$count - 1], $exceptions)) {
                  continue;
              }

              if (@chmod($fullpath, intval($mode, 8))) {
                  $_messages[] = sprintf('<success>%s changed to %s</success>', $fullpath, $mode);
              } else {
                  $_errors[] = sprintf('<error>%s NOT changed to %s</error>', $fullpath, $mode);
              }
          }
      }

      if (empty($_errors)) {
          return true;
      }
  }
  return false;
}

/**
 * Returns an array of nested directories and files in each directory
 *
 * @param string $path the directory path to build the tree from
 * @param array|boolean $exceptions Either an array of files/folder to exclude
 *   or boolean true to not grab dot files/folders
 * @param string $type either 'file' or 'dir'. null returns both files and directories
 * @return mixed array of nested directories and files in each directory
 */
function cli_tree($path, $exceptions = false, $type = null) {
	$files = array();
	$directories = array($path);

	if (is_array($exceptions)) {
		$exceptions = array_flip($exceptions);
	}
	$skipHidden = false;
	if ($exceptions === true) {
		$skipHidden = true;
	} elseif (isset($exceptions['.'])) {
		$skipHidden = true;
		unset($exceptions['.']);
	}

	try {
		$directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME | RecursiveDirectoryIterator::CURRENT_AS_SELF);
		$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
	} catch (Exception $e) {
		if ($type === null) {
			return array(array(), array());
		}
		return array();
	}

	foreach ($iterator as $itemPath => $fsIterator) {
		if ($skipHidden) {
			$subPathName = $fsIterator->getSubPathname();
			if ($subPathName{0} == '.' || strpos($subPathName, DS . '.') !== false) {
				continue;
			}
		}
		$item = $fsIterator->current();
		if (!empty($exceptions) && isset($exceptions[$item->getFilename()])) {
			continue;
		}

		if ($item->isFile()) {
			$files[] = $itemPath;
		} elseif ($item->isDir() && !$item->isDot()) {
			$directories[] = $itemPath;
		}
	}
	if ($type === null) {
		return array($directories, $files);
	}
	if ($type === 'dir') {
		return $directories;
	}
	return $files;
}