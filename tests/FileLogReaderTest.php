<?php 

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use Spir\FileLogReader\FileLogReader;

class FileLogReaderTest extends \PHPUnit_Framework_TestCase
{
    private $logPath;
    
    protected function setUp()
    {
        // log path used for tests
        $this->logPath = dirname(__FILE__) . '/tmpLogs';
        
        // just in case folder already exists
        if (file_exists($this->logPath)) 
        {
            $this->tearDown();
        }
        
        // create temporary folder that will store logs
        mkdir($this->logPath, 0700, TRUE);
        
        // create fake logs
        date_default_timezone_set('America/Los_Angeles');
        $filename = $this->logPath.'/log-'.php_sapi_name().'-'.date('Y-m-d').'.txt';
        $data  = '['.date('Y-m-d H:i:s').'] local.ERROR: Some error'."\n";
        $data .= '['.date('Y-m-d H:i:s').'] local.ERROR: Some error with stack'."\n";
        $data .= 'Stack trace:'."\n";
        $data .= '#0 Some more details goes here';
        
        file_put_contents($filename, $data);
    }

    protected function tearDown()
    {
        if (file_exists($this->logPath)) 
        {
            foreach(glob($this->logPath . '/*') as $file) 
            {
                unlink($file);
            } 
            rmdir($this->logPath); 
        }
    }
    
    public function testIsInstantiable()
    {
        // Given
        
        // When
        $instance = new FileLogReader;
        
        // Then
        // Nothing happens
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPathWithNoArgument()
    {
        // Given
        $fileLogReader = new FileLogReader;
        
        // When
        $fileLogReader->setPath();
        
        // Then
        // InvalidArgumentException
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPathWithEmptyArgument()
    {
        // Given
        $fileLogReader = new FileLogReader;
        $path = '';
        
        // When
        $fileLogReader->setPath($path);
        
        // Then
        // InvalidArgumentException
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPathWithFakeDirectory()
    {
        // Given
        $fileLogReader = new FileLogReader;
        $path = 'somewhere in space';
        
        // When
        $fileLogReader->setPath($path);
        
        // Then
        // InvalidArgumentException
    }
    
    public function testSetPathWithCorrectDirectory()
    {
        // Given
        $fileLogReader = new FileLogReader;
        $path = $this->logPath;
        
        // When
        $fileLogReader->setPath($path);
        
        // Then
        // Nothing happens
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetAllWithNoArgument()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi=null;
        
        // When
       $result = $fileLogReader->getAll($sapi);
        
        // Then
        // InvalidArgumentException
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetAllWithEmptyArgument()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi='';
        
        // When
       $result = $fileLogReader->getAll($sapi);
        
        // Then
        // InvalidArgumentException
    }
    
    public function testGetAllWithFakeSapi()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = 'Whatever';
        
        // When
       $result = $fileLogReader->getAll($sapi);
        
        // Then
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result)==0);
    }
    
    public function testGetAllWithCorrectSapi()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = php_sapi_name();
        
        // When
       $result = $fileLogReader->getAll($sapi);
        
        // Then
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result)==1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetFileWithNoArgument()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi=null;
        $date=null; 
        $level=null;
    
        // When
        $result = $fileLogReader->getFile($sapi, $date);
    
        // Then
        // InvalidArgumentException
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetFileWithEmptyArgument()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi='';
        $date=null;
    
        // When
        $result = $fileLogReader->getFile($sapi, $date);
    
        // Then
        // InvalidArgumentException
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetFileWithWrongLevel()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = 'SAPI';
        $date = new DateTime();
        $level = 'exploded';
    
        // When
        $result = $fileLogReader->getFile($sapi, $date, $level);
    
        // Then
        // RuntimeException
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetFileWithFakeSapi()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = 'Whatever';
        $date = new DateTime();
    
        // When
        $result = $fileLogReader->getFile($sapi, $date);
    
        // Then
        // RuntimeException
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetFileWithCorrectSapiButWrongDate()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = php_sapi_name();
        $date = new DateTime('1960-10-20');
    
        // When
        $result = $fileLogReader->getFile($sapi, $date);
    
        // Then
        // RuntimeException
    }
    
    public function testGetFileWithCorrectParameters()
    {
        // Given
        $fileLogReader = new FileLogReader($this->logPath);
        $sapi = php_sapi_name();
        $date = new DateTime();
    
        // When
        $result = $fileLogReader->getFile($sapi, $date);
    
        // Then
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result)==2);
        
        $this->assertArrayHasKey('level', $result[0]);
        $this->assertArrayHasKey('header', $result[0]);
        $this->assertArrayHasKey('stack', $result[0]);
        
        $this->assertArrayHasKey('level', $result[1]);
        $this->assertArrayHasKey('header', $result[1]);
        $this->assertArrayHasKey('stack', $result[1]);
    }
	
}
