<?php namespace Spir\FileLogReader;

use \DateTime;

interface FileLogReaderInterface
{
    
    /**
     * Set the path to the log files
     * 
     * @access public
     * @param string $path
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setPath($path=null);

    /**
     * Get all the log files for a given Server Application Programming Interface
     * 
     * @access public
     * @param string $sapi
     * @throws RuntimeException
     * @return array
     */
    public function getAll($sapi=null);
    
    /**
     * Get file content or null if file not found
     * 
     * @access public
     * @param string $sapi
     * @param DateTime $date
     */
    public function getFile($sapi=null, DateTime $date=null, $level='all');
    
    /**
     * Delete a log file for a given SAPI and date
     * 
     * @access public
     * @param string $sapi
     * @param DateTime $date
     * @throws Exception
     */
    public function deleteFile($sapi=null, DateTime $date=null);

    /**
     * Get the log levels from psr/log.
     * 
     * @access public
     * @return array
     */
    public function getLevels();

}
