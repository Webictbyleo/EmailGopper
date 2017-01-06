<?php
defined('SAFE')or die('Not allowed');

class File
{
	// Some properties.
	
	protected $_file;
	protected $lineEnding = "\n";
	protected $mode;
	protected $canRead;
	protected $canWrite;
	protected $csvDelimiter = ',';
	protected $csvEnclosure = '"';
	protected $csvEscape = '\\';
	protected $defaultPermissions = 0777;
	public $pointer;
	
	// Constructor.
	
	public function __construct($filename, $mode = '') {
		
		$this->_file = ($filename instanceof File) ? $filename->path : $filename;
		$this->_file = implode('/',array_filter(explode('/',str_replace('\\','/',$this->path))));
		$this->mode = $mode;
			if(is_dir($this->_file) AND !file_exists($this->_file.'/index.html')){
				
				file_put_contents($this->_file.'/index.html','<!DOCTYPE html><title></title>');
			}
	}
	
	public function __toString() {
		return $this->_file;
	}
	
	public function __get($name) {
		switch ($name)
		{
			case 'path':
				return $this->_file;
				
			case 'name':
				return basename($this->_file);
				
			case 'filename':
				return pathinfo($this->_file, PATHINFO_FILENAME);

			case 'extension':
				return pathinfo($this->_file, PATHINFO_EXTENSION);
				
			case 'type':
				return filetype($this->_file);

			case 'parent':
				return new File(dirname($this->_file));

			case 'exists':
				return file_exists($this->_file);

			case 'creatable':
				$path = $this->_file;
				while (!file_exists($path)) $path = dirname($path); //if the file doesn't exist, work backwards until we find a folder that does.
				return is_writable($path);
			
			case 'writable':
				return is_writable($this->_file);
				
			case 'readable':
				return is_readable($this->_file);

			case 'is_dir':
				return file_exists($this->_file)?is_dir($this->_file):false;

			case 'is_uploaded':
				return file_exists($this->_file)?is_uploaded_file($this->_file):false;
				
			case 'is_symlink':
				return is_link($this->_file);
				
			case 'is_hidden':
				if ($this->name[0]==='.') return true;
				return false;

			case 'size':
				return sprintf("%u", filesize($this->_file)); //formatting as string to avoid signed integer overflow
								
			case 'position':
				return ftell($this->pointer);
			
			case 'stat':
				if (!$this->pointer) $this->open();
				return fstat($this->pointer);

			case 'eof':
				if (!$this->pointer) $this->open();
				return feof($this->pointer);
				
			case 'children':
				if (!$this->exists || !$this->is_dir) return false;
				$results = array();
				if ($dh = opendir($this->_file)) {
					while (($file = readdir($dh)) !== false) if ($file != "." && $file != "..") $results[] = new File($this->_file.'/'.$file);
					closedir($dh);
				}
				return $results;
				
			case 'mimetype':
				// The first 4k should be enough for content checking
			
				if(function_exists('finfo_open')) {
			$resource = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($resource, $this->_file);
			finfo_close($finfo);
			
			return $mime;
		}
		if(Registry::getInstance()->isRegistered('/dev/mimetypes') ==false || !(Registry::getInstance()->isRegistered('/dev/mimetypes') AND !is_array(Registry::getInstance()->get('/dev/mimetypes')))){
		Director::profile('spyc');
		$mimes = Spyc::YAMLLoad(OPATH_ADMIN.DELIMITER_DIR.'dev/mimetypes.yml');
			Registry::getInstance()->set('/dev/mimetypes',$mimes);
			}else{
				$mimes = Registry::getInstance()->get('/dev/mimetypes');
			}
				//get the list of mimetypes stored locally
		$types = $mimes['HTTP']['MimeTypes'];
		
		//if this extension exissts in the types
		if(isset($types[$this->extension])) {
			//return the mimetype
			return $types[$this->extension];
		}
		
		//return text/plain by default
		return $types['class'];
				
			case 'time_modified':
				return filemtime($this->_file);

			case 'time_created':
				return filectime($this->_file);

			case 'time_last_accessed':
				return fileatime($this->_file);
			case 'shortname':
				$rp = $this->_file;
					if(stripos($rp,SITE) !==false){
						$rp = substr($rp,strlen(SITE));
					}
				return md5(str_replace('\\','/',$rp).DELIMITER_DOT.SYS_DOMAIN_HOST);
			case 'is_open_basedir_restricted':
			/**
			 * 	Checks if access to the resource is denied by the open_basedir php setting
			 */
				if (!ini_get('open_basedir')) return false; //no open_basedir is defined, all paths allowed
				
				$open_basedirs = explode(':', ini_get('open_basedir'));
				$found = FALSE;
				foreach ($open_basedirs as $open_basedir) {
					if (strpos($path, $open_basedir) === 0) {
						return false; //path is listed in the setting and is allowed.
					}
				}
				
				return TRUE; //path was not found in the setting, assumed it is restricted.
				
				

			// Nonexistent properties.
			default: return null;
		}
	}
	
	public function setLineEnding($lineEnding) {
		// Check the line ending.
		if (!in_array($lineEnding, array("\n", "\r\n")))
			throw new FileException('Line ending must be either \\n or \\r\\n.');

		// Keep.		
		$this->lineEnding = $lineEnding;
	}
	
	public function descendentOf($path) {
		
		$path = realpath(($path instanceof File) ? $path->path : $path);
		
		return (stripos($this->path, $path) === 0);
	}
	
	public function verifyParent() {
		if ($this->exists) return true;
		if (file_exists(dirname($this->_file))) return true;
		if ($this->creatable) {
			//if the file doesn't exist and the parent folder doesn't exist, test if the file can be created
			//if it can, create the parent folder structure
			$mk = mkdir(dirname($this->_file) , $this->defaultPermissions, true );
			
			return $mk;
		}
		return false;
	}
	
	public function get() {
		if (!$this->exists) throw new FileNotFoundException('File not found: ' . $this->_file);
		return file_get_contents($this->_file);
	}
	
	public function put($data, $append=false) {
		if ($this->pointer) $this->close();
		return $this->verifyParent()?file_put_contents($this->_file, $data, $append?FILE_APPEND:0):false;
	}
	
	public function append($data) {
		return $this->put($data, true);
	}
		
	public function copy($new) {
		if (!$this->exists) throw new FileNotFoundException('File not found: ' . $this->_file);
		

		if (is_string($new)) {
			if(is_dir($new)){
		$new = func_get_arg(0).'/'.basename($this->_file);
			}
			
			$new = new File($new);}
		
		if (!$new->verifyParent()) return false;
			
		if (copy($this->_file, $new)) {
			
			return new File($new);
		} else return false;
	}

	public function move($new) {
		if (!$this->exists) throw new FileNotFoundException('File not found: ' . $this->_file);
		if ($this->pointer) $this->close();
		
		if (is_string($new)) {if(is_dir($new)){
		$new = func_get_arg(0).'/'.basename($this->_file);
			};$new = new File($new);}
		if (!$new->verifyParent()) return false;
		
		if (rename($this->_file, $new)) {
			$this->_file = $new;
			return true;
		} else return false;
	}
	
	public function moveUploaded($new) {
		if (!$this->exists) throw new FileNotFoundException('File not found: ' . $this->_file);
		if ($this->pointer) $this->close();

		if (is_string($new)) $new = new File($new);
		if (!$new->verifyParent()) return false;
		
		if (move_uploaded_file($this->_file, $new)) {
			$this->_file = $new;
			return true;
		} else return false;
	}
	
	public function rename($new) {// alias to move() 
		$this->move($new);
	}
	
	public function delete() {
		if (!$this->exists) return true;
		if ($this->pointer) $this->close();
		
		//if it's just a normal file, delete it and end
		if (!$this->is_dir)	return unlink($this->_file);
		
		//resource is a folder, do a recursive delete
		
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_file), RecursiveIteratorIterator::CHILD_FIRST);

		for ($dir->rewind(); $dir->valid(); $dir->next()) {
		    if ($dir->isDir()) rmdir($dir->getPathname());
		    else unlink($dir->getPathname());
		}
		
		return rmdir($this->_file);
	}
	
	public function unlink() {// alias to delete() 
		return $this->delete();
	}
		
	public function touch() {
		return touch($this->_file);
	}
	
	public function seek($offset, $whence = SEEK_SET) {
		if (!$this->pointer) $this->open();
		return fseek($this->pointer, $offset, $whence);
	}
	
	public function rewind() {
		if (!$this->pointer) $this->open();
		return rewind($this->pointer);
	}
	
	public function lock($operation) {
		if (!$this->pointer) $this->open();
		return flock($this->pointer, $operation);
	}
	
	public function read($bytes) {
		if (!$this->pointer) $this->open();
		if (!$this->canRead) throw new FileInvalidAccessMode('File is not open for reading: ' . $this->_file);
		return fread($this->pointer, $bytes);
	}
	
	public function readLine() {
		if (!$this->pointer) $this->open();
		if (!$this->canRead) throw new FileInvalidAccessMode('File is not open for reading: ' . $this->_file);
		return fgets($this->pointer);
	}
	
	public function readRow() {
		if (!$this->pointer) $this->open();
		if (!$this->canRead) throw new FileInvalidAccessMode('File is not open for reading: ' . $this->_file);
		return fgetcsv($this->pointer, 0, $this->csvDelimiter, $this->csvEnclosure);
	}
	
	public function countRows() {
		$handle   = fopen($this->_file, 'r');
		$c = 0;
		while ( !feof ($handle) ) { //loop through each line
			fgets($handle);
			$c++;
		}
		fclose($handle);
		return $c;
	}
	
	public function tail($lines = 10) {
        $data = '';
        $fp = fopen($this->_file, "r");
        $block = 4096;
        $max = filesize($this->_file);

        for($len = 0; $len < $max; $len += $block) {
            $seekSize = ($max - $len > $block) ? $block : $max - $len;
            fseek($fp, ($len + $seekSize) * -1, SEEK_END);
            $data = fread($fp, $seekSize) . $data;

            if(substr_count($data, "\n") >= $lines + 1) {
                /* Make sure that the last line ends with a '\n' */
                if(substr($data, strlen($data)-1, 1) !== "\n") {
                    $data .= "\n";
                }

                preg_match("!(.*?\n){". $lines ."}$!", $data, $match);
                fclose($fp);
                return $match[0];
            }
        }
        fclose($fp);
        return $data; 
    }
	
	/**
	 * Returns a human readable file size in B/K/M/G/T
	 * 
	 * @author     Will Bond [wb] <will@flourishlib.com>
	 * @author     Alex Leeds [al] <alex@kingleeds.com>
	 * @author     Will Bond, iMarc LLC [wb-imarc] <will@imarc.net>
	 * @param  integer $decimal_places  The number of decimal places to display
	 * @return string
	 */
	public function getFormattedSize($decimal_places=1) {
		$bytes = $this->size;
		$suffixes  = array('B', 'KB', 'MB', 'GB', 'TB');
		$sizes     = array(1, 1024, 1048576, 1073741824, 1099511627776);
		$suffix    = (!$bytes) ? 0 : floor(log($bytes)/6.9314718);
		return number_format($bytes/$sizes[$suffix], ($suffix == 0) ? 0 : $decimal_places) . ' ' . $suffixes[$suffix];
	}
	
	public function write($data) {
		if (!$this->pointer) $this->open();
		if (!$this->canWrite) throw new FileInvalidAccessMode('File is not open for writing: ' . $this->_file);
		return fwrite($this->pointer, $data);
	}
	
	public function writeLine($data) {
		if (!$this->pointer) $this->open();
		if (!$this->canWrite) throw new FileInvalidAccessMode('File is not open for writing: ' . $this->_file);
		return fwrite($this->pointer, $data . $this->lineEnding);
	}

	public function writeRow($data) {
		if (!$this->pointer) $this->open();
		if (!$this->canWrite) throw new FileInvalidAccessMode('File is not open for writing: ' . $this->_file);
		return fputcsv($this->pointer, $data, $this->csvDelimiter, $this->csvEnclosure);
	}
	
	public function flush() {
		if (!$this->pointer) $this->open();
		if (!$this->canWrite) throw new FileInvalidAccessMode('File is not open for writing: ' . $this->_file);
		return fflush($this->pointer);
	}
	
	public function passthru() {
		if (!$this->pointer) $this->open();
		if (!$this->canRead) throw new FileInvalidAccessMode('File is not open for reading: ' . $this->_file);
		return fpassthru($this->pointer);
	}
	
	/**
	 * Prints the contents of the file
	 * 
	 * This method is primarily intended for when PHP is used to control access
	 * to files.
	 * 
	 * Be sure to turn off output buffering and close the session, if open, to
	 * prevent performance issues. 
	 * 
	 * @author Will Bond, iMarc LLC [wb-imarc] <will@imarc.net>
	 * @param  boolean $headers   If HTTP headers for the file should be included
	 * @param  mixed   $filename  Present the file as an attachment instead of just outputting type headers - if a string is passed, that will be used for the filename, if `TRUE` is passed, the current filename will be used
	 * @return fFile  The file object, to allow for method chaining
	 */
	public function output($headers=true, $filename=NULL) {
		if (ob_get_level() > 1) throw new FileInvalidAccessMode('File cannot be output when output buffering is enabled.');
		
		if ($headers) {
			if ($filename !== NULL) {
				if ($filename === TRUE) { $filename = $this->name;	}
				header('Content-Disposition: attachment; filename="' . $filename . '"');		
			}
			header('Cache-Control: ');
			header('Content-Length: ' . $this->size);
			header('Content-Type: ' . $this->mimetype);
			header('Expires: ');
			header('Last-Modified: ' . date('D, d M Y H:i:s', $this->time_modified));
			header('Pragma: ');	
		}
		
		readfile($this->_file);
		
	}
	
	public function truncate($size) {
		if (!$this->pointer) $this->open();
		if (!$this->canWrite) throw new FileInvalidAccessMode('File is not open for writing: ' . $this->_file);
		return ftruncate($this->pointer, $size);
	}
		
	public function open($mode=null) {
		if ($mode) $this->mode = $mode;
		if (!$this->mode) $this->mode = 'r';
		
		// If mode is 'r', check that the file exists.
		if (strpos($this->mode, 'r') !== false && !file_exists($this->_file))
			throw new FileNotFoundException('File not found: ' . $this->_file);
		
		// If mode is 'x', check that the file does not exist.		
		if (strpos($this->mode, 'x') !== false && file_exists($this->_file))
			throw new FileAlreadyExistsException('File already exists: ' . $this->_file);
			
		switch ($this->mode) {
			case 'r':	$this->canRead = true;$this->canWrite = false;break;
			
			case 'w':
			case 'a':
			case 'c':	$this->canRead = false;$this->canWrite = true;break;
			
			case 'r+':
			case 'w+':
			case 'x+':
			case 'c+':	$this->canRead = true;$this->canWrite = true;break;
		}

		if ($this->canWrite) { //file is being opened for writing
			if (!$this->verifyParent()) throw new FileException('Could not create directory structure: '. $this->_file);
		}

		$this->pointer = fopen($this->_file, $this->mode);
        if (!$this->pointer) throw new FileException('Failed to open file: '. $this->_file);
		
	}
		
	public function close() {
		fclose($this->pointer);
		$this->pointer = null;
	}
	
	public function __destruct() {
		@fclose($this->pointer);
	}
	
	
	public static function inst($path){
		if(isset($path)){
			return new file($path);
		}
	}
	
	
	
	public static function GetTemporary() {
		return new File(tempnam(sys_get_temp_dir(), 'tmp'));
	}
	
	
}

// Exceptions.

class FileException extends Exception { }
class FileNotFoundException extends FileException { }
class FileAlreadyExistsException extends FileException { }
class FileInvalidAccessMode extends FileException { }
