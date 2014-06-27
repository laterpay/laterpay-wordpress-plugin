<?php

class AbstractController {

    /**
     * Variables for substitution in templates
     */
    public $variables = array();

    public function loadAssets() {}

    /**
     * Render HTML file
     *
     * @param string $file file to get HTML string
     *
     * @access public
     */
    public function render( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        require_once LATERPAY_GLOBAL_PATH . "application/views/$file.php";
    }

    /**
     * Assign variable for substitution in templates
     *
     * @param string $variable name variable to assign
     * @param any    $value    value variable for assign
     *
     * @access public
     */
    public function assign( $variable, $value ) {
        $this->variables[$variable] = $value;
    }

    /**
     * Get HTML from file
     *
     * @param string $file file to get HTML string
     * @access public
     *
     * @return string html string
     */
    public function getTextView( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        ob_start();
        include LATERPAY_GLOBAL_PATH . "application/views/$file.php";
        $thread = ob_get_contents();
        ob_end_clean();
        $html = $thread;

        return $html;
    }

}
