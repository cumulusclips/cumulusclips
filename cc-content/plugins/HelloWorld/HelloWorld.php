<?php

class HelloWorld {

    public function Load() {
        Plugin::Attach ( 'view.header' , array( __CLASS__ , 'SayHelloWorld' ) );
    }

    static function Info() {
        return array(
            'plugin_name'   => 'Hello World',
            'author'        => 'CumulusClips.org',
            'version'       => '1.0'
        );
    }

    static function Settings() {}



    static function SayHelloWorld() { echo '<h1>Hello World!</h1>'; }

}

?>