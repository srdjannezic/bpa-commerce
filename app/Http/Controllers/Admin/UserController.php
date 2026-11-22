<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Set up the user service for account administration.
     */
    public function __construct(private UserService $users) {}

    /**
     * Show the paginated list of user accounts.
     */
    public function index(): View
    {
        return view('admin.users.index', ['users' => $this->users->all()]);
    }

    /**
     * Show the form for creating a user account.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Create an account using the validated role and user details.
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->users->create($request->validated());

        return to_route('admin.users.index')->with('status', 'Korisnik je dodat.');
    }
}
