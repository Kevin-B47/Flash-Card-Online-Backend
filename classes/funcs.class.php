<?php


class GFuncs{
        
        static function ReturnError($msg){
            $arr = array(
                "error"=>-1,
                "msg"=>$msg
            );
            return json_encode($arr); 
        }
}
?>