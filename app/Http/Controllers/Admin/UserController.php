<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    public function index(): View
    {
        return view('admin.users.index', ['users' => $this->users->all()]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->users->create($request->validated());

        return to_route('admin.users.index')->with('status', 'Korisnik je dodat.');
    }
}
