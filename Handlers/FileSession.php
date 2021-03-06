<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Support\Finder;
use Syscodes\Support\Chronos;
use Syscodes\Filesystem\Filesystem;

/**
 * Session handler using file system for storage.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class FileSession implements SessionHandlerInterface
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Filesystem\filesystem $files
     */
    protected $files;

    /**
     * The file name.
     * 
     * @var string $path
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     * 
     * @var int $minutes
     */
    protected $minutes;

    /**
     * Constructor. The FileSession class instance.
     * 
     * @param  \Syscodes\Filesystem\filesystem  $file
     * @param  string  $path
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(Filesystem $file, $path, $minutes)
    {
        $this->files   = $file;
        $this->path    = $path;
        $this->minutes = $minutes;
    }    
    
    /**
     * Open session name.
     * 
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }
    
    /**
     * Close session.
     * 
     * @return bool
     */
    public function close()
    {
        return true;
    }
    
    /**
     * Reads session data and acquires a lock.
     * 
     * @param  string  $sessionId
     * 
     * @return string
     */
    public function read($sessionId)
    {
        if ($this->files->isFile($path = $this->path.DIRECTORY_SEPARATOR.$sessionId)) {
            if (filemtime($path) >= Chronos::now()->subMinutes($this->minutes)->getTimestamp()) {
                return $this->files->get($path);
            }
        }
        
        return '';
    }
    
    /**
     * Writes (create / update) session data.
     * 
     * @param  string  $sessionId
     * @param  string  $data
     * 
     * @return bool
     */
    public function write($sessionId, $data)
    {
        $this->files->put($this->path.DIRECTORY_SEPARATOR.$sessionId, $data, true);

        return true;
    }
    
    /**
     * Destroys the current session.
     * 
     * @param  string  $sessionId
     * 
     * @return bool
     */
    public function destroy($sessionId)
    {
        $this->files->delete($this->path.DIRECTORY_SEPARATOR.$sessionId);

        return true;
    }
    
    /**
     * Deletes expired sessions.
     * 
     * @param  int  $lifetime
     * 
     * @return bool
     */
    public function gc($lifetime)
    {
        $files = Finder::render($this->path);

        foreach ($files as $file) {
            if ($this->files->lastChange($file) + $lifetime < time() && $this->files->exists($file)) {
                $this->files->delete($file);
            }
        }
    }
}