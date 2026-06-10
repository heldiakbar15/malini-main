<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function customer()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('dashboard/customer');
    }

    public function admin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return view('dashboard/admin');
    }
}