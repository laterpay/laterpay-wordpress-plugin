<?php

class Response extends Entity {

    public function _construct() {
        parent::_construct();
        $this->setData( 'headers', array ( ) );
        $this->setData( 'body', '' );
        $this->setData( 'http_response_code', 200 ); // HTTP response code to use in headers
    }

    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param string  $name
     * @return string
     */
    protected function _normalizeHeader( $name ) {
        $filtered = str_replace( array ( '-', '_' ), ' ', (string) $name );
        $filtered = ucwords( strtolower( $filtered ) );
        $filtered = str_replace( ' ', '-', $filtered );

        return $filtered;
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string  $name
     * @param string  $value
     * @param boolean $replace
     * @return Response
     */
    public function setHeader( $name, $value, $replace = false ) {
        $name       = $this->_normalizeHeader( $name );
        $value      = (string) $value;
        $headers    = $this->getDataSetDefault( 'headers', array ( ) );

        if ( $replace ) {
            foreach ( $headers as $key => $header ) {
                if ( $name == $header['name'] ) {
                    unset( $headers[$key] );
                }
            }
        }
        $headers[] = array (
            'name'      => $name,
            'value'     => $value,
            'replace'   => $replace
        );
        $this->setData( 'headers', $headers );

        return $this;
    }

    /**
     * Send all headers
     *
     * Sends any headers specified.
     *
     * @return Response
     */
    public function sendHeaders() {
        if ( headers_sent() ) {
            return $this;
        }
        $httpCodeSent = false;
        foreach ( $this->getDataSetDefault( 'headers', array ( ) ) as $header ) {
            if ( !$httpCodeSent ) {
                header( $header['name'] . ': ' . $header['value'], $header['replace'], $this->getData( 'http_response_code' ) );
                $httpCodeSent = true;
            } else {
                header( $header['name'] . ': ' . $header['value'], $header['replace'] );
            }
        }

        if ( !$httpCodeSent ) {
            header( 'HTTP/1.1 ' . $this->getData( 'http_response_code' ) );
            $httpCodeSent = true;
        }

        return $this;
    }

    /**
     * Set HTTP response code to use with headers
     *
     * @param int     $code
     * @return Response
     */
    public function setHttpResponseCode( $code ) {
        if ( !is_int( $code ) || ( 100 > $code ) || ( 599 < $code ) ) {
            $code = 500;
            return $this;
        }

        $this->setData( 'http_response_code', $code );

        return $this;
    }

    /**
     * Echo the body segments
     *
     * @return void
     */
    public function outputBody() {
        $body = $this->getData( 'body' );
        if ( is_array( $body ) ) {
            $body = implode( '', $body );
        }

        echo $body;
    }

    public function sendResponse() {
        $this->sendHeaders();
        $this->outputBody();
    }

}
