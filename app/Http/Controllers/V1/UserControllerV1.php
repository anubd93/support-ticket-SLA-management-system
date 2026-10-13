<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreUserRequestV1;
use App\Http\Requests\V1\UpdateUserRequestV1;
use App\Services\V1\UserServiceV1;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerV1 extends Controller
{
    public function __construct(private readonly UserServiceV1 $userServiceV1) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['keyword', 'role']);

        return view('users.index', [
            'users' => $this->userServiceV1->getUsers($filters),
            'filters' => $filters,
        ] + $this->userServiceV1->getFormOptions());
    }

    public function create(): View
    {
        return view('users.create', ['user' => null] + $this->userServiceV1->getFormOptions());
    }

    public function store(StoreUserRequestV1 $request): JsonResponse
    {
        try {
            $user = $this->userServiceV1->create($request->validated());

            return response()->json([
                'message' => "User {$user->name} created.",
                'redirect' => route('users.index'),
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function edit(int $id): View
    {
        $user = $this->userServiceV1->findUser($id);
        abort_if(! $user, Response::HTTP_NOT_FOUND);

        return view('users.edit', ['user' => $user] + $this->userServiceV1->getFormOptions());
    }

    public function update(UpdateUserRequestV1 $request, int $id): JsonResponse
    {
        abort_if(! $this->userServiceV1->findUser($id), Response::HTTP_NOT_FOUND);

        try {
            $this->userServiceV1->update($id, $request->validated(), $request->user());

            return response()->json([
                'message' => 'User updated.',
                'redirect' => route('users.index'),
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
