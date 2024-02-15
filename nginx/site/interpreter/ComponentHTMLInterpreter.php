<?php
    
    namespace interpreter;
    
    /**
     * This class is responsible for providing the HTML code of the components from provided data.
     */
    class ComponentHTMLInterpreter
    {
        
        /**
         * Checks the type of the component to be translated and calls the appropriate method to interpret it.
         *
         * @param $componentData array A JSON array containing the data of the component.
         *
         * @return string The HTML code of the interpreted component.
         */
        public function interpretJsonComponent(array $componentData): string
        {
            
            return match ($componentData['type']) {
                'text' => $this->interpretTextComponent($componentData),
                'image' => $this->interpretImageComponent($componentData),
                'video' => $this->interpretVideoComponent($componentData),
                default => '',
            };
        }
        
        /**
         * Interprets the text component and returns the HTML element.
         *
         * @param $componentData array A JSON array containing the data of the component.
         *
         * @return string The HTML code of the interpreted component.
         */
        private function interpretTextComponent(array $componentData): string
        {
            
            $title = array_key_exists('title', $componentData) ? $componentData['title'] : '';
            $titleSize = array_key_exists('title-size', $componentData) ? $componentData['title-size'] : '2em';
            $titleColor = array_key_exists('title-color', $componentData) ? $componentData['title-color'] : 'var(--tertiary)';
            $titleAlignment = array_key_exists('title-alignment', $componentData) ? $componentData['title-alignment'] : 'left';
            $titleDecorations = array_key_exists('title-decorations', $componentData) ? $componentData['title-decorations'] : '';
            $contentSize = array_key_exists('content-size', $componentData) ? $componentData['content-size'] : '1em';
            $contentDecorations = array_key_exists('decorations', $componentData) ? $componentData['decorations'] : '';
            $contentColor = array_key_exists('color', $componentData) ? $componentData['color'] : 'var(--tertiary)';
            $contentAlignment = array_key_exists('alignment', $componentData) ? $componentData['alignment'] : 'left';
            
            $element = "<div class='text-component'><h3 style='font-size: $titleSize; color: $titleColor; text-align: $titleAlignment; text-decoration: $titleDecorations;'>$title</h3>";
            $element .= "<p style='font-size: $contentSize; color: $contentColor; text-align: $contentAlignment; text-decoration: $contentDecorations;'>{$componentData['content']}</p>";
            
            return $element . "</div>";
        }
        
        /**
         * Interprets the image component and returns the HTML element.
         *
         * @param $componentData array A JSON array containing the data of the component.
         *
         * @return string The HTML code of the interpreted component.
         */
        private function interpretImageComponent(array $componentData): string {
            
            return "<img class='img-component' src='{$componentData['source']}' alt='{$componentData['text']}''>";
        }
        
        /**
         * Interprets the video component and returns the HTML element.
         *
         * @param $componentData array A JSON array containing the data of the component.
         *
         * @return string The HTML code of the interpreted component.
         */
        private function interpretVideoComponent(array $componentData): string{
            
            return "<video class='video-component' controls>
                        <source src='{$componentData['source']}' type='video/mp4'>
                    </video>";
        }
    }