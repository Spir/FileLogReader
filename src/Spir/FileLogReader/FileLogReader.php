<?php namespace Spir\FileLogReader;

use \DateTime;
use \RuntimeException;
use \InvalidArgumentException;
use \ReflectionClass;
use Psr\Log\LogLevel;

class FileLogReader implements FileLogReaderInterface 
{
    
    /**
     * Log directory
     * 
     * @var string
     */
    protected $path;
    
    public function __construct($path=null)
    {
        if (!empty($path) && !is_null($path))
            $this->setPath($path);
    }
    
    /**
     * Set the path to the log files
     * 
     * @access public
     * @param string $path
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setPath($path=null)
    {
        if (empty($path) || is_null($path))
            throw new InvalidArgumentException('Log path is wrong');
        
        // Check if path is valid
        if (!is_dir($path))
            throw new InvalidArgumentException('Log path is wrong');
        
        $this->path = realpath($path);
    }

    /**
     * Get all the log files for a given Server Application Programming Interface
     * 
     * @access public
     * @param string $sapi
     * @throws RuntimeException
     * @return array
     */
    public function getAll($sapi=null)
    {
        if (empty($sapi) || is_null($sapi))
            throw new InvalidArgumentException('Log sapi not set');
        
        if (is_null($this->path))
            throw new RuntimeException('Log path not set'); // shall we use the Laravel default one?
        
        $fileNamePattern = $this->path.'/log-'.$sapi.'*.txt';
        
        return glob($fileNamePattern);
    }
    
    /**
     * Get file content or null if file not found
     * 
     * @access public
     * @param string $sapi
     * @param DateTime $date
     * @throws Exception
     */
    public function getFile($sapi=null, DateTime $date=null, $level='all')
    {
        $levels = $this->getLevels();
            
        if ($level!='all' && !in_array($level, $levels))
            throw new RuntimeException('Wrong level');
            
        try 
        {
            // log pattern
            $pattern = "/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/";
            
            $levels = $this->getLevels();
            $logs = array();
            
            $file = $this->findFile($sapi, $date);
            
            $content = file_get_contents($file);
            
            $found = preg_match_all($pattern, $content, $headings);
            
            if ($found == 0 || count($headings)==0)
                return $logs;
            
            $headings = $headings[0];
            
            $logData = preg_split($pattern, $content);
            
            // first line is most likely empty
            if ($logData[0] < 1) {
                $trash = array_shift($logData);
                unset($trash);
            }
            
            for ($i=0, $j = count($headings); $i < $j; $i++) 
            {
                foreach ($levels as $l) 
                {
                    if ($level != $l && $level != 'all')
                        continue;

                    if (strpos(strtolower($headings[$i]), strtolower('.'.$l)))
                    {
                        // removing the date from the log error since it's obvious
                        $header = str_replace($date->format('Y-m-d').' ', '', $headings[$i]);
                        
                        $logs[] = array('level' => $l, 'header' => $header, 'stack' => $logData[$i]);
                    }
                }
            }
            return $logs;
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
    
    /**
     * Delete a log file for a given SAPI and date
     * 
     * @access public
     * @param string $sapi
     * @param DateTime $date
     * @throws Exception
     */
    public function deleteFile($sapi=null, DateTime $date=null)
    {
        try 
        {
            $file = $this->findFile($sapi, $date);
            unlink($file);
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
    
    /**
     * Find a file for a given SAPI and date
     * 
     * @param string $sapi
     * @param DateTime $date
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return string
     */
    private function findFile($sapi=null, DateTime $date=null)
    {
        if (empty($sapi) || is_null($sapi))
            throw new InvalidArgumentException('Log sapi not set');
        
        if (is_null($date) || !($date instanceof DateTime))
            throw new InvalidArgumentException('Given date is not correct');
        
        if (is_null($this->path))
            throw new RuntimeException('Log path not set'); // shall we use the Laravel default one?
        
        $fileNamePattern = $this->path.'/log-'.$sapi.'-'.$date->format('Y-m-d').'.txt';
        
        $file = glob($fileNamePattern);
        
        if (count($file)==0)
            throw new RuntimeException('File not found');
        
        return $file[0];
    }

    /**
     * Get the log levels from psr/log.
     * 
     * @access public
     * @return array
     */
    public function getLevels()
    {
        $class = new ReflectionClass(new LogLevel);
        return $class->getConstants();
    }
    
}
