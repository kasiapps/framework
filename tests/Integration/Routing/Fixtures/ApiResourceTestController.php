<?php

namespace Kasi\Tests\Integration\Routing\Fixtures;

use Kasi\Routing\Controller;

class ApiResourceTestController extends Controller
{
    public function index()
    {
        return 'I`m index';
    }

    public function store()
    {
        return 'I`m store';
    }

    public function show()
    {
        return 'I`m show';
    }

    public function update()
    {
        return 'I`m update';
    }

    public function destroy()
    {
        return 'I`m destroy';
    }
}
