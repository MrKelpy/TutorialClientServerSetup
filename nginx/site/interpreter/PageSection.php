<?php
    
    namespace interpreter;
    
    /**
     * This class is responsible for holding and representing a section in the page system json file.
     */
    class PageSection
    {
        /**
         * @var string The name of the section.
         */
        public string $name ;
        
        /**
         * @var string The identification namespace of the section, used for the index.
         */
        public string $namespace;
        
        /**
         * @var string The position of the section in the navigation bar.
         */
        public string $position;
        
        /**
         * @var bool Whether the section is visible or not.
         */
        public bool $visible = true;
        
        /**
         * @var bool Whether the section has an index on page 0 or not.
         */
        public bool $index = true;
        
        /**
         * General constructor for the PageSection class.
         * @param array $json The raw json data for the section.
         */
        public function __construct(array $json)
        {
            $this->name = $json['name'];
            $this->namespace = $json['namespace'];
            $this->position = $json['position'];
            $this->visible = array_key_exists('visible', $json) ? $json['visible'] : true ;
            $this->index = array_key_exists('index', $json) ? $json['index'] : true ;
        }
    }