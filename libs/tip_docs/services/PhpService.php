<?php
namespace tip_docs\services;

use tip\services\base\BaseService;
use lithium\analysis\Inspector;
use lithium\analysis\Docblock;

class PhpService extends BaseService
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected function _toClassPath($class){
        return is_object($class) ? get_class($class) : $class;
    }

    public function classInfo($class,$options =array()){
        return  Inspector::info($this->_toClassPath($class),$options);
    }

    public function classMethods($class,$format=null,$options=array()){
        $options += array('public'=>false);
        return Inspector::methods($class,$format,$options);
    }

    public function classProperties($class,$options=array()){
        $options += array('public'=>false);
        return Inspector::properties($class,$options);
    }

    public function classLines($class,$lines){
        return Inspector::lines($this->_toClassPath($class),$lines);
    }


    public function classContent($class,$showFullClass=false,$returnAsString=true){
        $info = $this->classInfo($class,array('start','end'));
        $start = $info['start'];
        $end = $info['end'];
        if(!$showFullClass){
            $start++;
            $end--;
        }
        $content = $this->classLines($class,$this->fillRange($start,$end));
        return $returnAsString ? implode(is_string($returnAsString) ? $returnAsString : "\n",$content ) : $content;

    }

    public function methodInfo($class,$method, $info=array()){
        $class = $this->_toClassPath($class);
        return Inspector::info("$class::$method",$info);
    }

    public function propertyInfo($class,$property, $info=array()){
        $class = $this->_toClassPath($class);
        if(strpos($property,'$') !== 0 )$property = '$'.$property;
        return Inspector::info("$class::$property",$info);
    }

    public function methodParams($class,$method,$convert=true){
        $class = $this->_toClassPath($class);
        $m = new \ReflectionMethod($class,$method);
        $params = $m->getParameters();
        if($convert){
            $new = array();
            foreach($params as $param){
                $new[] = $param->name;
            }
            return $new;
        }
        return $params;
    }

    public function methodContent($class,$method,$showFullMethod=false,$returnAsString=true){
        $methodLines = $this->classMethods($class,'ranges',array('methods'=>$method));
        if($methodLines){
            $methodLines = $methodLines[$method];
            if($showFullMethod){
                array_unshift($methodLines,$methodLines[0]-1);
                $methodLines[] = $methodLines[count($methodLines)-1]+1;
            }
        }
        $content = $this->classLines($class,$methodLines);
        return $returnAsString ? implode(is_string($returnAsString) ? $returnAsString : "\n",$content ) : $content;
    }


    public function fillRange($rangeStart,$rangeEnd=null){
        if(is_array($rangeStart)){
            $rangeEnd = $rangeStart[1];
            $rangeStart = $rangeStart[0];
        }
        $range = array();
        for($i=$rangeStart;$i<=$rangeEnd;$i++){
            $range[] = $i;
        }
        return $range;
    }


}
