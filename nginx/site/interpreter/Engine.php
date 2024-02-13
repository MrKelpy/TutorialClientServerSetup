<?php
    namespace interpreter;
    
    /**
     * This class is responsible for providing the main endpoints for the interpretation of the json pages,
     * interacting with the other interpreter resources to return HTML code to be placed in the website.
     *
     * This is also the main class of the interpreter. As such, the lingo of all the others and this one is defined
     * in this document.
     *
     * - Component: A declared json mapping related to html elements, yet to be interpreted.
     * - Element: The HTML code for a given component, defined in the json as per the formatting file.
     */
    class engine
    {
        
        /**
         * Builds the navigation elements, to be placed in the middle of the header, from the declared
         * section components in the json page system.
         *
         * @return string The html code string for the navigation elements.
         */
        public function buildNavigationElements(): string {
            
            // Read the sections from the page system.
            $reader = new PageSystemReader();
            $sections = $reader->readSections();
            $navigation = '';
            
            // Build the navigation elements from the section components.
            foreach (array_keys($sections) as $section_name) {
                
                $section = $sections[$section_name];
                if (!$section->visible) continue;
                
                $namespace = str_replace('.', '+', $section->namespace);
                $navigation .= "<a href='index.php?section=$namespace&page=0'>$section_name</a>";
            }
            
            return $navigation;
        
        }
        
        /**
         * Builds the web page for a given section and position (page), from the declared
         * section components in the json page system.
         * @param string $section The name of the section to build the page for.
         * @param string $page The position of the section to build the page for.
         *
         * @return void The html code string for the page.
         */
        public function buildPage(string $section_namespace, string $page): string {
            
            // Gets the section from the namespace.
            $reader = new PageSystemReader();
            $section = $reader->getSectionByNamespace($section_namespace);
            
            // If the page is 0, build the index.
            if ($page == 0 && $section->index) return $this->buildIndex($section);
            
            // Otherwise, build the page in itself.
            return "page";
        }
        
        /**
         * Builds the index for a given section, to be placed in the middle of the page, from the declared
         * section components in the json page system.
         *
         * @param PageSection $section The section to build the index for.
         * @return string The html code string for the index.
         */
        public function buildIndex(PageSection $section): string {
            
            // Read the sections from the page system.
            $reader = new PageSystemReader();
            $pages = $reader->getPagesForSection($section);
            $index = "<ol><li><h4>$section->name</h4></li>";
            
            // Build the index from the section components.
            
            
            return $index . "</ol>";
        }
        
        /**
         * Builds the hyperlink bar that stays at the bottom of the page, used to browse through
         * the different pages of a section.
         * @param string $section_name The name of the section to build the hyperlink bar for.
         * @param int $current_page The position of the current page in the section.
         *
         *
         * @return string The html code string for the hyperlink bar.
         */
        public function buildSectionNavigation(string $section_name, int $current_page): string {
            
            // Read the sections from the page system.
            $reader = new PageSystemReader();
            $sections = $reader->readSections();
            $section = $sections[$section_name];
            $pages = $reader->getPagesForSection($section);
            $navigation = '';
            
            // If there is only one page, there is no need for a navigation bar.
            if (count($pages) == 1) return "";
            
            // Adds the back button if there is a previous page.
            $back = $current_page - 1;
            
            if ($back > 0)
                $navigation .= "<a class='button-href' href='index.php?section=home&page=$back'>Back</a>";
            
            // For as many pages as there are in the section, build a hyperlink.
            for ($i = 0; $i < count($pages); $i++) {
                $navigation .= "<a href='index.php?section=$section_name&page=$i'>$i </a>|";
            }
            
            // Adds the next button if there is a next page and returns the navigation bar.
            $next = $current_page + 1;
            
            if ($next < count($pages))
                $navigation .= "<a class='button-href' href='index.php?section=home&page=$next'>Next</a>";
            
            return $navigation;
        }
    }