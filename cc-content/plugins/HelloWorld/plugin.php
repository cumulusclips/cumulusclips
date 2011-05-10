<?php

class HelloWorld {

    public function Load() {
//        Plugin::Attach ( 'view.body' , array( 'HelloWorld' , 'SayHelloWorld' ) );
    }

    static function SayHelloWorld() { echo '<h1>Hello World!</h1>'; }
    static function Info() { /* Info Array */ }

}

?>