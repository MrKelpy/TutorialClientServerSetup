<?php
    
    namespace interpreter;
    
    /**
     * This class is responsible for holding and representing a page in the page system json file. It will
     * also provide methods used to set layers of abstraction over the raw json data to make the html
     * building process easier.
     */
    class PageJson
    {
        
        /**
         * @var PageSection The section associated with the page.
         */
        public PageSection $section;
        
        
        /**
         * @var string The full page namespace, including the subsections on the original section's namespace.
         * If there is no subsection, this will be the same as the section namespace.
         */
        public string $pageNamespace;
        
        
        /**
         * @var string The name of the page subsection, if any. If there is no subsection, this will be the same
         * as the section name.
         */
        public string $pageSubsectionName;
        
        
        /**
         * @var int The position of the page in the section.
         */
        public int $pagePosition;
        
        
        /**
         * @var array The raw json content of the page.
         */
        public array $content;
        
        /**
         * Constructs the PageJson object setting the root section provided and assigning the page
         * json data to the object.
         * @param array       $json The raw json data for the page.
         * @param PageSection $section The section associated with the page.
         */
        public function __construct(array $json, PageSection $section)
        {
            $this->section = $section;
            $this->pageNamespace = $json['section']['namespace'];
            $this->pageSubsectionName = array_key_exists('subsection-name', $json['section']) ? $json['section']['subsection-name'] : $section->name;
            $this->pagePosition = $json['section']['position'];
            $this->content = $json['content'];
        }
        
        
        
    }