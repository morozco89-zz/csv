<?php

namespace morozco\csv;

use morozco\csv\Exceptions\InvalidHandleException;
use morozco\csv\Exceptions\UnreadableHandleException;
use morozco\csv\Exceptions\CantUseBinarySearchException;
/**
 * Description of CSVBinarySearch
 *
 * @author morozco
 */
class CSVBinarySearch {
    protected $key;
    protected $columnSeparator = ',';
    protected $keyIndex;
    protected $breakLine = "\n";
    protected $fileStats = array();
    protected $handle;
    protected $debug;
    protected $sleepTime = 1;
    
    const LESS = 0;
    const MORE = 1;
    
    public function __construct($fileHandle, $keyIndex, $debug = false) {
        $this->handle = $fileHandle;

        if(@get_resource_type($this->handle) !== 'stream'){
            throw new InvalidHandleException();
        }
        
        $meta = stream_get_meta_data($this->handle);

        if($meta['mode'] !== 'r'){
            throw new UnreadableHandleException();
        }
        
        $this->keyIndex = $keyIndex;
        $this->fileStats = array_slice(fstat($this->handle), 13);
        $this->debug = $debug;
    }
    
    public function __destruct(){
        @fclose($this->handle);
    }
    
    public function setKey($key){
        $this->key = $key;
    }
    
    public function setColumnSeparator($columnSeparator){
        $this->columnSeparator = $columnSeparator;
    }
    
    public function setSleepTime($sleepTime){
        $this->sleepTime = $sleepTime;
    }
    
    public function setDebug($debug){
        $this->debug = $debug;
    }
    
    public function setBreakline($bl){
        $this->breakLine = $bl;
    }
    
    public function getLine(){
        $current = ftell($this->handle);
    
        while ($this->breakLine !== ($char = fgetc($this->handle))) {
            if(ftell($this->handle) - 2 > 0){
                $success = fseek($this->handle, -2, SEEK_CUR);
            } else {
                $success = fseek($this->handle, 0, SEEK_SET);
                break;
            }
            
            if($success === -1){
                return null;
            }
        }

        $line = fgetcsv($this->handle, 0, $this->columnSeparator);
        fseek($this->handle, $current, SEEK_SET);

        return $line;
    }
    
    public function getDataFromNextLine($limit = 0){
        $offset = ftell($this->handle);
        while($this->breakLine !== ($char = fgetc($this->handle))) {
            $success = fseek($this->handle, $offset++);
            
            if($success === -1){
                return null;
            }
        }
        
        if($limit === 0){
            return fread($this->handle, $this->fileStats['size']);
        } else {
            return fread($this->handle, $limit);
        }
    }
    
    public function search(){
        
        $inicio = 0;
        $fin = $this->fileStats['size'];
        $stopCondition = -1;
        $iterations = 0;
        $lastKey = null;
        
        if($this->debug){
            echo 'Iniciando busqueda de: ' . $this->key . PHP_EOL;
        }
        
        while ($stopCondition != 0) {
            $iterations++;
            $stopCondition = floor(($fin - $inicio) / 2);

            fseek($this->handle, $inicio + floor(($fin - $inicio) / 2), SEEK_SET);

            if($this->debug){
                echo 'Iteracion: ' . $iterations . PHP_EOL;
                echo 'Inicio: ' . $inicio . PHP_EOL;
                echo 'Fin: ' . $fin . PHP_EOL;
                echo 'Moviendo puntero a: ' . ($inicio + $stopCondition) . PHP_EOL;
            }

            $line = $this->getLine();
            
            $keyFound = $line[$this->keyIndex];
            
            if(!is_null($lastKey) && $expectedKeyValue === self::LESS  && $keyFound > $lastKey){
                throw new CantUseBinarySearchException();
            }
            
            if(!is_null($lastKey) && $expectedKeyValue === self::MORE  && $keyFound < $lastKey){
                throw new CantUseBinarySearchException();
            }
            
            $lastKey = $keyFound;

            if ($keyFound == $this->key) {
                return $line;
            } else if ($keyFound > $this->key) {
                if($this->debug){
                    echo 'Llave encontrada (' . $keyFound .') es MAYOR' . PHP_EOL;
                }
                
                $expectedKeyValue = self::LESS;
                $fin = ftell($this->handle);
            } else {
                if($this->debug){
                    echo 'Llave encontrada (' . $keyFound .') es MENOR' . PHP_EOL;
                }
                
                $expectedKeyValue = self::MORE;
                $inicio = ftell($this->handle);
            }

            if($this->debug){
                echo '___________________________________' . PHP_EOL;
                sleep($this->sleepTime);
            }
        }
        
        return null;
    }
    
    /**
     * 
     * @return type
     * @throws CantUseBinarySearchException
     */
    public function searchLastData($limit = 0){
        
        $inicio = 0;
        $fin = $this->fileStats['size'];
        $stopCondition = -1;
        $iterations = 0;
        $lastKey = null;
        
        if($this->debug){
            echo 'Iniciando busqueda de: ' . $this->key . PHP_EOL;
        }
        
        while ($stopCondition != 0) {
            $iterations++;
            $stopCondition = floor(($fin - $inicio) / 2);

            fseek($this->handle, $inicio + floor(($fin - $inicio) / 2), SEEK_SET);

            if($this->debug){
                echo 'Iteracion: ' . $iterations . PHP_EOL;
                echo 'Inicio: ' . $inicio . PHP_EOL;
                echo 'Fin: ' . $fin . PHP_EOL;
                echo 'Moviendo puntero a: ' . ($inicio + $stopCondition) . PHP_EOL;
            }

            $line = $this->getLine();
            
            $keyFound = $line[$this->keyIndex];
            
            if(!is_null($lastKey) && $expectedKeyValue === self::LESS  && $keyFound > $lastKey){
                throw new CantUseBinarySearchException();
            }
            
            if(!is_null($lastKey) && $expectedKeyValue === self::MORE  && $keyFound < $lastKey){
                throw new CantUseBinarySearchException();
            }
            
            $lastKey = $keyFound;

            if ($keyFound == $this->key) {
                return $this->getDataFromNextLine($limit);
            } else if ($keyFound > $this->key) {
                if($this->debug){
                    echo 'Llave encontrada (' . $keyFound .') es MAYOR' . PHP_EOL;
                }
                
                $expectedKeyValue = self::LESS;
                $fin = ftell($this->handle);
            } else {
                if($this->debug){
                    echo 'Llave encontrada (' . $keyFound .') es MENOR' . PHP_EOL;
                }
                
                $expectedKeyValue = self::MORE;
                $inicio = ftell($this->handle);
            }

            if($this->debug){
                echo '___________________________________' . PHP_EOL;
                sleep($this->sleepTime);
            }
        }
        
        return null;
    }
}
