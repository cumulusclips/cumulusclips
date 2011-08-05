<?php

class HelloWorld {

    public function Load() {
//        Plugin::Attach ( 'view.body' , array( 'HelloWorld' , 'SayHelloWorld' ) );
    }

    static function SayHelloWorld() { echo '<h1>Hello World!</h1>'; }
    static function Info() {
        return array(
            'plugin_name'   => 'Hello World',
            'author'        => 'CumulusClips.org'
        );
    }

}

?>