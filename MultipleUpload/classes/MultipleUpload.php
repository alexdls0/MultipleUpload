<?php
class MultipleUpload {
    
    const POLICY_KEEP = 1,
            POLICY_OVERWRITE = 2,
            POLICY_RENAME = 3,
            MIN_OWN_ERROR = 1000;

    private $generalError = false,
            $files,
            $maxSize = 0,
            $name,
            $policy = self::POLICY_KEEP,
            $target = '',
            $type = '';
            
    function __construct($input) {
        if(isset($_FILES[$input])) {
            $this->files = $_FILES[$input];
            $this->files = $this->redesignArray();
            $this->depureFiles($this->files);
        } else {
            $this->generalError = true;
        }
    }
    
    function getFiles(){
        return $this->files;
    }
    
    function getGeneralError() {
        return $this->generalError;
    }
    
    function getMaxSize() {
        return $this->maxSize;
    }
    
    function getTarget() {
        return $this->target;
    }
    
    function setPolicy($policy) {
        if(is_int($policy) && $policy >= self::POLICY_KEEP && $policy <= self::POLICY_RENAME) {
            $this->policy = $policy;
        }
        return $this;
    }
    
    function setTarget($target) {
        if(is_string($target) && trim($target)!==''){
            $this->target = trim($target);
        }
    }
    
    function setType($type) {
        if(is_string($type) && trim($type)!==''){
            $this->type = trim($type);
        }
    }
    
    function setName($name){
        if(is_string($name) && trim($name)!==''){
            $this->name = trim($name);
            for($i=0 ; $i < count($this->files) ; $i++){
                $this->files[$i]['name']=$name;
            }
        }
    }
    
    function setMaxSize($size) {
        if(is_int($size) && $size > 0) {
            $this->maxSize = $size;
        }
        return $this;
    }
    
    function isValidSize() {
        return ($this->maxSize === 0 || $this->maxSize >= $this->file['size']);
    }
    
    function redesignArray(){
        $redesign = array();
        $count = count($this->files['name']);
        $keys = array_keys($this->files);
            
        for ($i=0; $i<$count; $i++) {
            foreach ($keys as $key) {
                $redesign[$i][$key] = $this->files[$key][$i];
            }
        }
        return $redesign;
    }
    
    function depureFiles($arrayOfFiles){
        $longitud = count($arrayOfFiles);
        for($i=0 ; $i < $longitud; $i++){
            if($arrayOfFiles[$i]['name']==''){
                unset($arrayOfFiles[$i]);
                $arrayOfFiles=array_values($arrayOfFiles);
                $i--;
                $longitud--;
            }
        }
        $this->files=$arrayOfFiles;
    }
    
    function upload() {
        $result = false;
        if($this->generalError===false) {
            $result = $this->__uploadRedesignedArray();
        }
        return $result;
    }
    
    private function __uploadRedesignedArray(){
        $result = 0;
        for($i=0 ; $i < count($this->files) ; $i++){
            $this->__doUpload($i);
        }
        return $result;
    }
    
    private function __doUpload($position) {
        $result = false;
        switch($this->policy) {
            case self::POLICY_KEEP:
                $result = $this->__doUploadKeep($position);
                break;
            case self::POLICY_OVERWRITE:
                $result = $this->__doUploadOverwrite($position);
                break;
            case self::POLICY_RENAME:
                $result = $this->__doUploadRename($position);
                break;
        }
        return $result;
    }
    
    private function __doUploadOverwrite($position){
        return move_uploaded_file($this->files[$position]['tmp_name'], $this->target . '/' . $this->files[$position]['name']);
    }
    
    private function __doUploadKeep($position){
        $result = false;
        $name = $this->files[$position]['name'];
        if(!file_exists($this->target .'/'. $name)) {
            $result = move_uploaded_file($this->files[$position]['tmp_name'], $this->target .'/'. $this->files[$position]['name']);
        } else {
            $this->savedNames[$position] = 'error';
        }
        return $result;
    }
    
    function isValidType($position){
        $valid = true;
        if($this->type !==''){
            $tipo = shell_exec('file --mime ' .$this->files[$position]['tmp_name']);
            $posicion = strpos($tipo, $this->type);
            if($posicion === false){
                $valid = false;
            }
        }
        return $valid;
    }
    
    private function __doUploadRename($position){
        $rute = $this->target.'/'.$this->files[$position]['name'];
        if(file_exists($rute)) {
            $rute = self::__getValidName($rute);
        }
        $result = move_uploaded_file($this->files[$position]['tmp_name'], $rute);
        return $result;
    }
    
    private static function __getValidName($rute) {
        $parts = pathinfo($rute);
        $extension = '';
        if(isset($parts['extension'])) {
            $extension = '.' . $parts['extension'];
        }
        $cont = 1;
        while(file_exists($parts['dirname'] . '/' . $parts['filename'] . $cont . $extension)) {
            $cont++;
        }
        return $parts['dirname'] . '/' . $parts['filename'] . $cont . $extension;
    }
}