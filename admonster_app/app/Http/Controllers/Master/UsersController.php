<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/07/29
 * Time: 17:37
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        return view('master.users.index');
    }
}
