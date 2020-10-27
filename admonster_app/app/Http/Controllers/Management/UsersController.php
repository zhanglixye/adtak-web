<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/09/17
 * Time: 18:05
 */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        return view('users.index');
    }
}
