<?php namespace App\SUtils;

    class SAuxEvent {
        public $dtDate;
        public $typeId;
        public $typeName;
        
        public function __construct() {
            $this->dtDate = null;
            $this->typeId = 0;
            $this->typeName = "";
        }
    }

?>