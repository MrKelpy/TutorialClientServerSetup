<?php
    
    namespace interpreter;
    
    use ParseError;
    
    /**
     * This class is responsible for reading the page system json file and returning the data to the engine. For
     * the reader to work, the json files must be placed inside the /pages directory, and the data will be returned
     * in the form of associative arrays.
     */
    class PageSystemReader
    {
        
        /**
         * @var string The path to the /pages directory.
         */
        private string $pagesPath;
        
        /**
         * General constructor for the PageSystemReader class.
         */
        public function __construct() {
            $this->pagesPath = $_SERVER['DOCUMENT_ROOT'] . '/pages';
        }
        
        /**
         * Recursively scans the directory and returns all the files in it.
         *
         * @param $target string The directory to be scanned.
         *
         * @return array The paths of all the files in the directory.
         */
        public function recursiveScandir(string $target): array
        {
            // Gets all the new files in the current directory.
            $files = array_diff(scandir($target), ['.', '..']);
            $allFiles = [];
            
            foreach ($files as $file) {
                $fullPath = $target. DIRECTORY_SEPARATOR .$file;
                is_dir($fullPath) ? array_push($allFiles, ...$this->recursiveScandir($fullPath)) : array_push($allFiles, $fullPath);
            }
            
            return $allFiles;
        }
        
        /**
         * Gets all the json files in the /pages directory.
         * @return array The paths of all the json files in the /pages directory.
         */
        public function getAllJson(): array {
            
            $jsonFiles = array();
            $files = $this->recursiveScandir($this->pagesPath);
            
            // Filter the files to only include the json files.
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json')
                    $jsonFiles[] = $file;
            }
            
            return $jsonFiles;
        }
        
        /**
         * Reads and returns all the defined sections in the page system json files as
         * associative arrays mapping the section names to PageSection objects.
         *
         * @return array The associative array of PageSection objects.
         * @throws ParseError If there are duplicate section namespaces.
         */
        public function readSections(): array {
            
            $sections = array();
            $jsonFiles = $this->getAllJson();
            
            // Keeps track of the registered namespaces for error checking.
            $registered_namespaces = array();
            
            // Read the json files and set them into PageSection objects assigned to the section names.
            foreach ($jsonFiles as $file) {
                
                // Decode the json file and check if it is a section. If not, skip it.
                $json = json_decode(file_get_contents($file), true);
                if ($json['type'] != 'section') continue;
                
                // We can't have duplicate section namespaces.
                if (in_array($json['namespace'], $registered_namespaces))
                    throw new ParseError("Duplicate section namespace: " . $json['namespace'] . "defined in file: " . $file);
                
                // We can't have duplicate section names.
                if (array_key_exists($json['name'], $sections))
                    throw new ParseError("Duplicate section name: " . $json['name'] . "defined in file: " . $file);
                
                $sections[$json['name']] = new PageSection($json);
            }
            
            // Sort the sections by their position.
            $positions = array_column($sections, 'position');
            array_multisort($positions, SORT_ASC, $sections);
            
            return $sections;
        }
        
        /**
         * Gets a section by its namespace. If the section is not found, returns false.
         * @param string $namespace The namespace of the section to get.
         *
         * @return PageSection|bool The section object or false if the section is not found.
         */
        public function getSectionByNamespace(string $namespace): PageSection|bool
        {
            
            $jsonFiles = $this->readSections();
            
            // Look for the section with the given namespace.
            foreach ($jsonFiles as $section) {
                if ($section->namespace == $namespace) return $section;
            }
            
            return false;
        }
        
        /**
         * Reads and returns all the defined pages for a section in the page system as associative arrays
         * mapping the page positions to the page objects.
         * @param PageSection $section The section to get the pages for.
         *
         * @return array The associative array of page data.
         */
        public function getPagesForSection(PageSection $section): array {
            
            $pages = array();
            $jsonFiles = $this->getAllJson();
            
            // Read the json files and set them into PageSection objects assigned to the section names.
            foreach ($jsonFiles as $file) {
                
                // Decode the json file and check if it is a section. If not, skip it.
                $json = json_decode(file_get_contents($file), true);
                if ($json['type'] != 'page') continue;
                
                // Get the root page namespace by grabbing the first nesting level
                $root_page_namespace = $json['section']['namespace'];
                
                if (str_contains($json['section']['namespace'], "."))
                    $root_page_namespace = explode(".", $root_page_namespace)[0];
                
                // If the page is not for the section, skip it.
                if ($root_page_namespace != $section->namespace) continue;
                
                $pages[strval($json['section']['position'])] = new PageJson($json, $section);
            }
            
            // Sort the pages by their position.
            ksort($pages);
            
            return $pages;
        }
    }