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
                
                $navigation .= "<a href='index.php?section=$section->namespace&page=0'>$section_name</a>";
            }
            
            return $navigation;
        
        }
        
        /**
         * Builds the web page for a given section and position (page), from the declared
         * section components in the json page system.
         *
         * @param string $section The name of the section to build the page for.
         * @param string $pagePosition The position of the section to build the page for.
         *
         * @return void The html code string for the page.
         */
        public function buildPage(string $section_namespace, string $pagePosition): string {
            
            // Gets the section from the namespace.
            $reader = new PageSystemReader();
            $section = $reader->getSectionByNamespace($section_namespace);
            $pages = $reader->getPagesForSection($section);
            $pagePosition = intval($pagePosition);
            
            // If the page is 0, build the index.
            if ($pagePosition == 0 && $section->index) return $this->buildIndex($section);
            if (count($pages) == 0) return "";
            
            // Otherwise, build the page in itself.
            $root = array_keys($pages[$pagePosition]->content)[0];
            return $this->_internalBuildPage($pages[$pagePosition]->content[$root], $pages[$pagePosition]->content[$root]['type']);
        }
        
        /**
         * Builds the page from the content array, which is the result of the interpretation of the json page system.
         * This method is recursive.
         * @param array $content The content array to build the page from.
         * @param string $rootType Either 'column' or 'row', depending on the root type of the page.
         *
         * @return string The html code string for the page.
         */
        private function _internalBuildPage(array $content, string $rootType): string {
            
            $page = "";
            
            $spaceBelow = array_key_exists('space-below', $content) ? $content['space-below'] : 0;
            $spaceAbove = array_key_exists('space-above', $content) ? $content['space-above'] : 0;
            
            $page .= str_repeat("<br>&nbsp;\n", $spaceAbove);
            $page .= "<div class='$rootType-root'>";
            $htmlInterpreter = new ComponentHTMLInterpreter();
            
            // Iterates over every component in the content array and adds its html code to the page.
            $ignore = ['type', 'space-below', 'space-above'];
            
            // Loops over every component in the content array to check for a column or row component.
            foreach (array_keys($content) as $component) {
                
                if (!is_array($content[$component])) continue;
                if (in_array($component, $ignore)) continue;
                
                // If the component is a column or row, recursively build the page from it.
                if ($content[$component]['type'] == 'column' || $content[$component]['type'] == 'row') {
                    $page .= $this->_internalBuildPage($content[$component], $content[$component]['type']);
                }
                
                $page .= $htmlInterpreter->interpretJsonComponent($content[$component]);
            }
            
            // If the root type is a column, add the column div.
            return $page . "</div>" . str_repeat("<br>&nbsp;\n", $spaceBelow);
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
            
            return "<div class='index'>". $this->_buildListRoot($section->name, $section->namespace, $pages) . "</div>";
        }
        
        /**
         * Builds an ordered list with all the pages in the section as list items. Recursively creates
         * sublists for the subsections.
         * @param string $sectionName The name of the section to build the list for.
         * @param string $sectionNamespace The namespace of the section to build the list for.
         * @param array  $pages The list of all the pages in the section.
         *
         * @return string The html code string for the list.
         */
        private function _buildListRoot(string $sectionName, string $sectionNamespace, array $pages): string {
            
            $currentNamespaceNesting = count(explode(".", $sectionNamespace));
            $startingPage = $this->_getStartingPage($pages, $sectionNamespace);
            $separationDots = str_repeat(".", 150 - strlen($sectionName)*1.5);
            $rootNamespace = explode(".", $sectionNamespace)[0];
            
            // Set the root of the list, depending on the nesting level of the current namespace.
            if ($currentNamespaceNesting == 1) $root = "<div class='index-title'><h3 class='index-header'>Índice - $sectionName</h3></div><ol><div class='inner-index'>\n";
            else $root = "<li><ol><a class='index-row' href='index.php?section=$rootNamespace&page=$startingPage'><div>$sectionName</div><div>$separationDots</div><div>$startingPage</div></a>\n";
            
            // Gets an associative array of all the subsection namespaces mapped to their pages
            $subsections = array();
            
            foreach ($pages as $page) {
                $subsections[$page->pageNamespace][] = $page;
            }
            
            // Handle the recursive addition of nested sub lists for the subsections.
            foreach (array_keys($subsections) as $sub) {
                
                // Get the nesting level of the current page and the page to be added. The nesting level of "foo.bar", for instance, is 2.
                $subNamespaceNesting = count(explode(".", $sub));
                
                // Check the current namespace is contained in the section namespace.
                $pageIsSubsection = str_contains($sub, $sectionNamespace);
                
                // Checks if the page is a direct subsection of the section.
                $isDirectSubsection = $subNamespaceNesting == $currentNamespaceNesting + 1;
                if (!($isDirectSubsection && $pageIsSubsection)) continue;
                
                // Recursively build the root sub lists nested to the current page.
                $root .= $this->_buildListRoot($subsections[$sub][0]->pageSubsectionName, $sub, $pages);
            }
            
            $closure = $currentNamespaceNesting == 1 ? "</div></ol>" : "</li></ol>";
            return $root . $closure;
        }
        
        /**
         * Gets the starting page for a given section.
         * @param array $pages The list of all the pages in the section.
         * @param string $sectionNamespace The namespace of the section to get the starting page for.
         *
         * @return int The starting page for the section.
         */
        private function _getStartingPage(array $pages, string $sectionNamespace): int {
            
            // Get the pages meant to be in the current section.
            $sectionPages = array_filter($pages, function ($page) use ($sectionNamespace) {
                return $page->pageNamespace == $sectionNamespace;
            });
            
            // Get the starting page, the lowest positioned page in the section.
            $starting_page = -1;
            
            foreach ($sectionPages as $page) {
                
                // If the position is 0, it is the starting page.
                if ($page->pagePosition == 0) {
                    $starting_page = $page;
                    break;
                }
                
                // If the position is lower than the current starting page, it is the new starting page.
                if ($page->pagePosition < $starting_page || $starting_page == -1) $starting_page = $page->pagePosition;
            }
            
            return $starting_page;
        }
        
        /**
         * Builds the hyperlink bar that stays at the bottom of the page, used to browse through
         * the different pages of a section.
         * @param string $section_namespace The namespace of the section to build the hyperlink bar for.
         * @param int $current_page The position of the current page in the section.
         *
         *
         * @return string The html code string for the hyperlink bar.
         */
        public function buildSectionNavigation(string $section_namespace, int $current_page): string {
            
            // Read the sections from the page system.
            $reader = new PageSystemReader();
            $sections = $reader->readSections();
            
            $section_name = $reader->getSectionByNamespace($section_namespace)->name;
            $section = $sections[$section_name];
            
            $pages = $reader->getPagesForSection($section);
            $navigation = '';
                $page_total = $section->index ? count($pages) + 1 : count($pages);
            
            // If there is only one page, there is no need for a navigation bar.
            if ($page_total <= 1) return "";
            
            // Adds the back button if there is a previous page.
            $back = $current_page - 1;
            
            // If there is a previous page, add the hyperlink for the button.
            $backHref = $back >= 0 ? "href='index.php?section=$section_namespace&page=$back'" : "";
            $navigation .= "<a class='button-href' $backHref>Back</a>";
            
            // For as many pages as there are in the section, build a hyperlink.
            for ($i = $section->index ? 0 : 1 ; $i < $page_total; $i++) {
                
                if ($i == $current_page) $navigation .= "<a class='selected' href='index.php?section=$section_namespace&page=$i'>$i </a>";
                else $navigation .= "<a href='index.php?section=$section_namespace&page=$i'>$i </a>";
            }
            
            // Adds the next button if there is a next page and returns the navigation bar.
            $next = $current_page + 1;
            
            // If there is a next page, add the hyperlink for the button.
            $nextHref = $next < $page_total ? "href='index.php?section=$section_namespace&page=$next'" : "";
            $navigation .= "<a class='button-href' $nextHref>Next</a>";
            
            return $navigation;
        }
    }