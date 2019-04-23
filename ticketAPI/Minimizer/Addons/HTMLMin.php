<?php
namespace Minimizer\Addons;

/**
 * Description of HTMLMin Class
 *
 * @author Enmanuel Bisono Payamps <enmanuel0894@gmail.com>
 */
class HTMLMin {
    
    protected $content = null;


    public static function minify( $html ){
        $minify = new HTMLMin( $html );
        return $minify -> min();
    }
    
    private function __construct( $content ) {
        $this -> content = $content;
    }
    
    protected function min(){
        
        if( !is_null( $this -> content ) ){
            if(trim($this -> content) === "") return $this -> content;
            // Remove extra white-space(s) between HTML attribute(s)
            $this -> content = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
                return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
            }, str_replace("\r", "", $this -> content));
            // Minify inline CSS declaration(s)
            /*if(strpos($this -> content, ' style=') !== false) {
                $this -> content = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                    return '<' . $matches[1] . ' style=' . $matches[2] . CSSMin::minify($matches[3]) . $matches[2];
                }, $$this -> content);
            }*/
            return preg_replace(
                array(
                    // t = text
                    // o = tag open
                    // c = tag close
                    // Keep important white-space(s) after self-closing HTML tag(s)
                    '#<(img|input)(>| .*?>)#s',
                    // Remove a line break and two or more white-space(s) between tag(s)
                    '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                    '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                    '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                    '#<(img|input)(>| .*?>)<\/\1\x1A>#s', // reset previous fix
                    '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                    // Force line-break with `&#10;` or `&#xa;`
                    '#&\#(?:10|xa);#',
                    // Force white-space with `&#32;` or `&#x20;`
                    '#&\#(?:32|x20);#',
                    // Remove HTML comment(s) except IE comment(s)
                    '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
                ),
                array(
                    "<$1$2</$1\x1A>",
                    '$1$2$3',
                    '$1$2$3',
                    '$1$2$3$4$5',
                    '$1$2$3$4$5$6$7',
                    '$1$2$3',
                    '<$1$2',
                    '$1 ',
                    "\n",
                    ' ',
                    ""
                ),
            $this -> content );
        }
        
        return '';
    }
}
