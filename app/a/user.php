<?php

namespace app\a;

use app\a\aa\admin;

class user
{

    public static function hello()
    {
        admin::hello();
        echo 'hello user<br>';
    }

}
