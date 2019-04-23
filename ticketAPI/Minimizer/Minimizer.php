<?php
namespace Minimizer;

use Minimizer\Addons\JSMin;
use Minimizer\Addons\CSSMin;
use Minimizer\Addons\HTMLMin;

/**
 * 
 */
class Minimizer {

    private $archivos = array ();

    public function __construct( $archivos = array() ) {
        $this -> archivos = $archivos;
    }
    /**
     * 
     * @param type $archivos
     * @return type
     */
    protected function getContents( $archivos ){
        $contenido = '';
        foreach( $archivos as $archivo ){
            $contenido .= file_get_contents( $archivo );
        }
        return $contenido;
    }
    
    public function minifyCss( $content ){
        if( count( $this -> archivos ) ){
            $content = $this -> getContents( $this -> archivos['css'] );
        }
        return CSSMin::minify( $content );
    }
    
    public function minifyJs( $content = '' ){
        if( count( $this -> archivos ) ){
            $content = $this -> getContents( $this -> archivos['js'] );
        }
        /**
         * Esta clase fue bajada de internet, porque para comprimir javascript hay que considerar más criterios
         * y esta se adapta perfectamente.
         *
         * Web: http://wonko.com/post/a_faster_jsmin_library_for_php
         */
        return JSMin::minify( $content );
    }
    
    public function minifyHtml( $content = '' ){
        if( count( $this -> archivos ) ){
            $content = $this -> getContents( $this -> archivos['html'] );
        }
        return HTMLMin::minify( $content );
    }

}
